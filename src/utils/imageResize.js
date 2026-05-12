const MAX_DIMENSION = 1920
const QUALITY = 0.92

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

function drawToBlob(img, maxDim, quality) {
  const ratio = Math.min(maxDim / img.width, maxDim / img.height, 1)
  const w = Math.max(1, Math.round(img.width * ratio))
  const h = Math.max(1, Math.round(img.height * ratio))
  const canvas = document.createElement('canvas')
  canvas.width = w
  canvas.height = h
  const ctx = canvas.getContext('2d')
  if (!ctx) throw new Error('Pildi töötlemine ebaõnnestus.')
  ctx.drawImage(img, 0, 0, w, h)
  return new Promise((resolve) => {
    canvas.toBlob((blob) => resolve(blob), 'image/jpeg', quality)
  })
}

export async function processImageFile(file) {
  if (!file || !file.type?.startsWith('image/')) {
    throw new Error('Palun vali pildifail.')
  }
  const img = await loadImage(file)
  const blob = await drawToBlob(img, MAX_DIMENSION, QUALITY)
  if (!blob) throw new Error('Pildi töötlemine ebaõnnestus.')
  return new File([blob], 'image.jpg', { type: 'image/jpeg' })
}
