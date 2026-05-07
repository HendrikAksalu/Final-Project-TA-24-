// Vähendab pildi mõõtmed brauseris enne backendisse saatmist.
// Eesmärk: hoida base64 imageUrl alla ~700 KB, et mahtuda Zone.ee
// nginx/PHP päringu limiitide alla.

const MAX_DIMENSION = 1280 // täispildi suurim külg
const THUMB_DIMENSION = 360 // pisipildi suurim külg
const INITIAL_QUALITY = 0.82
const MIN_QUALITY = 0.55
const MAX_BASE64_BYTES = 700_000 // ~700 KB base64 stringi pikkusena

function loadImage(file) {
  return new Promise((resolve, reject) => {
    const url = URL.createObjectURL(file)
    const img = new Image()
    img.onload = () => {
      URL.revokeObjectURL(url)
      resolve(img)
    }
    img.onerror = (err) => {
      URL.revokeObjectURL(url)
      reject(err)
    }
    img.src = url
  })
}

function drawToDataUrl(img, maxDim, quality) {
  const ratio = Math.min(maxDim / img.width, maxDim / img.height, 1)
  const w = Math.max(1, Math.round(img.width * ratio))
  const h = Math.max(1, Math.round(img.height * ratio))
  const canvas = document.createElement('canvas')
  canvas.width = w
  canvas.height = h
  const ctx = canvas.getContext('2d')
  if (!ctx) throw new Error('Pildi töötlemine ebaõnnestus.')
  ctx.drawImage(img, 0, 0, w, h)
  return canvas.toDataURL('image/jpeg', quality)
}

// Proovib mitu korda, vähendades kvaliteeti, kuni base64 mahub
// MAX_BASE64_BYTES alla. Kui ka miinimumkvaliteediga ei mahu,
// vähendab mõõtmeid 80% ja proovib uuesti.
function compressUntilFits(img, startMaxDim) {
  let maxDim = startMaxDim
  for (let attempt = 0; attempt < 4; attempt++) {
    let quality = INITIAL_QUALITY
    while (quality >= MIN_QUALITY) {
      const dataUrl = drawToDataUrl(img, maxDim, quality)
      if (dataUrl.length <= MAX_BASE64_BYTES) return dataUrl
      quality = Math.round((quality - 0.07) * 100) / 100
    }
    maxDim = Math.round(maxDim * 0.8)
  }
  // Viimase variandina anna minimaalse mõõtme ja kvaliteediga
  return drawToDataUrl(img, maxDim, MIN_QUALITY)
}

export async function processImageFile(file) {
  if (!file || !file.type?.startsWith('image/')) {
    throw new Error('Palun vali pildifail.')
  }
  const img = await loadImage(file)
  const imageUrl = compressUntilFits(img, MAX_DIMENSION)
  const imageThumbUrl = drawToDataUrl(img, THUMB_DIMENSION, 0.7)
  return { imageUrl, imageThumbUrl }
}
