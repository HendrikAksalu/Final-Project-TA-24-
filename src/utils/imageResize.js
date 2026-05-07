// Vahendab pildi mootu brauseris enne backendisse saatmist.

const MAX_DIMENSION = 1600
const THUMB_DIMENSION = 400
const QUALITY = 0.85

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
  const w = Math.round(img.width * ratio)
  const h = Math.round(img.height * ratio)
  const canvas = document.createElement('canvas')
  canvas.width = w
  canvas.height = h
  const ctx = canvas.getContext('2d')
  if (!ctx) throw new Error('Pildi tootle mine ebaonnestus.')
  ctx.drawImage(img, 0, 0, w, h)
  return canvas.toDataURL('image/jpeg', quality)
}

export async function processImageFile(file) {
  if (!file || !file.type?.startsWith('image/')) {
    throw new Error('Palun vali pildifail.')
  }
  const img = await loadImage(file)
  const imageUrl = drawToDataUrl(img, MAX_DIMENSION, QUALITY)
  const imageThumbUrl = drawToDataUrl(img, THUMB_DIMENSION, 0.7)
  return { imageUrl, imageThumbUrl }
}
