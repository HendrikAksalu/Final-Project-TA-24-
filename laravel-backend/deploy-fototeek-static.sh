#!/usr/bin/env bash
# Deploy Laravel+Vite build to Fototeek static hosting (ZoneOS / subdomain docroot).
#
# Prerequisites: npm run build (from this directory), SSH key access to host.
#
# Usage:
#   ./deploy-fototeek-static.sh virt137753@ta24aksalu.itmajakas.ee
# Or:
#   DEPLOY_HOST=virt137753@ta24aksalu.itmajakas.ee ./deploy-fototeek-static.sh
#
# Optional:
#   DEPLOY_SSH_KEY=~/.ssh/id_ed25519
#   DEPLOY_REMOTE_BASE='~/domeenid/www.ta24aksalu.itmajakas.ee/fototeek'
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

HOST="${DEPLOY_HOST:-${1:-}}"
KEY="${DEPLOY_SSH_KEY:-$HOME/.ssh/id_ed25519}"
# Important: use ~/... in the remote path so ~ expands on the SERVER (not your local $HOME).
REMOTE_BASE="${DEPLOY_REMOTE_BASE:-~/domeenid/www.ta24aksalu.itmajakas.ee/fototeek}"

if [[ -z "$HOST" ]]; then
  echo "Usage: DEPLOY_HOST=user@host $0   OR   $0 user@host" >&2
  echo "Easiest: cd laravel-backend && bash ./deploy-fototeek.sh" >&2
  exit 1
fi

if [[ ! -f public/build/manifest.json ]]; then
  echo "Missing public/build/manifest.json — run: npm run build" >&2
  exit 1
fi

TMP_INDEX="$(mktemp)"
trap 'rm -f "$TMP_INDEX"' EXIT

node --input-type=module -e "
import { readFileSync, writeFileSync } from 'node:fs';
const m = JSON.parse(readFileSync('public/build/manifest.json', 'utf8'));
const cssEntry = m['resources/css/app.css']?.file;
const jsEntry = m['resources/js/app.js'];
const jsFile = jsEntry?.file;
const cssFromJs = jsEntry?.css?.[0];
if (!cssEntry || !jsFile || !cssFromJs) {
  throw new Error('Unexpected manifest shape — check resources/css/app.css and resources/js/app.js entries');
}
const fp = [cssEntry, cssFromJs, jsFile].join('|');
const html = \`<!DOCTYPE html>
<html lang=\"et\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
  <meta http-equiv=\"Cache-Control\" content=\"no-cache, no-store, must-revalidate\" />
  <meta http-equiv=\"Pragma\" content=\"no-cache\" />
  <meta http-equiv=\"Expires\" content=\"0\" />
  <title>Fototeek</title>
  <!-- fototeek-build:\${fp} -->
  <style>html,body{margin:0;min-height:100vh;background:#f5f2ee}</style>
  <link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">
  <link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>
  <link href=\"https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..1,800&family=Inter:wght@400;500;600;700&display=swap\" rel=\"stylesheet\">
  <link rel=\"icon\" type=\"image/png\" href=\"/logo.png\">
</head>
<body>
  <div id=\"app\"></div>
  <script>
(function(){
  function stripCb(){
    try{
      var u=new URL(location.href);
      if(u.searchParams.has('_cb')){
        u.searchParams.delete('_cb');
        history.replaceState({},'',u.pathname+u.search+u.hash);
      }
    }catch(e){}
  }
  function boot(){
    var h=document.head;
    var l1=document.createElement('link');
    l1.rel='stylesheet';
    l1.crossOrigin='anonymous';
    l1.href='/\${cssEntry}';
    h.appendChild(l1);
    var l2=document.createElement('link');
    l2.rel='stylesheet';
    l2.crossOrigin='anonymous';
    l2.href='/\${cssFromJs}';
    h.appendChild(l2);
    var s=document.createElement('script');
    s.type='module';
    s.crossOrigin='anonymous';
    s.src='/\${jsFile}';
    document.body.appendChild(s);
  }
  function fpFromDoc(html){
    var m=html.match(/<!-- fototeek-build:([^>]+) -->/);
    return m&&m[1];
  }
  var mine=fpFromDoc(document.documentElement.innerHTML);
  fetch(location.pathname+location.search,{cache:'no-store',credentials:'same-origin'})
    .then(function(r){return r.text();})
    .then(function(ht){
      var remote=fpFromDoc(ht);
      if(remote && mine && remote!==mine){
        var u=new URL(location.href);
        if(!u.searchParams.has('_cb')){
          u.searchParams.set('_cb',String(Date.now()));
          location.replace(u.toString());
          return;
        }
      }
      if(remote && !mine){
        var u2=new URL(location.href);
        if(!u2.searchParams.has('_cb')){
          u2.searchParams.set('_cb',String(Date.now()));
          location.replace(u2.toString());
          return;
        }
      }
      stripCb();
      boot();
    })
    .catch(function(){stripCb();boot();});
})();
  <\/script>
</body>
</html>
\`;
writeFileSync(process.argv[1], html);
console.log('Generated index.html using:', { cssEntry, cssFromJs, jsFile });
" "$TMP_INDEX"

SCP_OPTS=(-i "$KEY" -o StrictHostKeyChecking=accept-new)

echo "Uploading assets → ${HOST}:${REMOTE_BASE}/assets/ ..."
scp "${SCP_OPTS[@]}" public/build/assets/* "${HOST}:${REMOTE_BASE}/assets/"

echo "Uploading assets → ${HOST}:${REMOTE_BASE}/current/public/assets/ ..."
scp "${SCP_OPTS[@]}" public/build/assets/* "${HOST}:${REMOTE_BASE}/current/public/assets/"

echo "Uploading index.html (both shells) ..."
scp "${SCP_OPTS[@]}" "$TMP_INDEX" "${HOST}:${REMOTE_BASE}/current/public/index.html"
scp "${SCP_OPTS[@]}" "$TMP_INDEX" "${HOST}:${REMOTE_BASE}/index.html"

echo "Done. Open / reload normally — shell auto-fixes stale cached HTML when deploy fingerprints differ."
