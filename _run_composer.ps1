$ErrorActionPreference = "Stop"
$log = "\\wsl.localhost\Ubuntu-24.04\home\hendrik\Final-Project-TA-24-\_shell_log.txt"
try {
  "start $(Get-Date)" | Out-File $log
  wsl -d Ubuntu-24.04 -e bash -c 'cd /home/hendrik/Final-Project-TA-24- && rm -rf _laravel_merge && composer create-project laravel/laravel _laravel_merge --no-interaction 2>&1' | Out-File -Append $log
  "exit $LASTEXITCODE" | Out-File -Append $log
} catch {
  $_ | Out-File -Append $log
}
