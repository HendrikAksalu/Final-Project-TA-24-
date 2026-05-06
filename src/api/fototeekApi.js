function resolveApiBaseUrl() {
  const fromEnv = String(import.meta.env.VITE_API_BASE_URL || '').trim().replace(/\/$/, '')
  if (fromEnv) return fromEnv
  if (typeof window !== 'undefined' && window.location?.origin) {
    return window.location.origin.replace(/\/$/, '')
  }
  return 'http://localhost:8000'
}

const API_BASE_URL = resolveApiBaseUrl()

export const TOKEN_KEY = 'fototeek_token'

export function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}

export function setToken(token) {
  if (token) {
    localStorage.setItem(TOKEN_KEY, token)
  } else {
    localStorage.removeItem(TOKEN_KEY)
  }
}

export async function apiFetch(path, options = {}) {
  const headers = { Accept: 'application/json', ...(options.headers || {}) }
  const token = getToken()
  if (token) {
    headers.Authorization = `Bearer ${token}`
  }

  let body = options.body
  if (body !== undefined && body !== null && typeof body === 'object' && !(body instanceof FormData)) {
    headers['Content-Type'] = 'application/json'
    body = JSON.stringify(body)
  }

  const url = `${API_BASE_URL}/api${path.startsWith('/') ? path : `/${path}`}`
  return fetch(url, { ...options, headers, body })
}

export async function parseApiError(response, fallbackMessage) {
  try {
    const payload = await response.json()
    if (payload?.message) return payload.message
    if (payload?.errors && typeof payload.errors === 'object') {
      const firstError = Object.values(payload.errors)[0]
      if (Array.isArray(firstError) && firstError[0]) return firstError[0]
    }
  } catch (error) {
    // ignore
  }
  return fallbackMessage || 'Viga.'
}

export function normalizeMemoryFromApi(row) {
  if (!row) return null
  return {
    id: row.id,
    title: row.title || '',
    photoClass: row.photoClass || 'one',
    favorite: Boolean(row.favorite),
    rotate: row.rotate || '',
    story: row.story || '',
    who: row.who || '',
    when: row.when || '',
    where: row.where || '',
    imageUrl: row.imageUrl || '',
    imageThumbUrl: row.imageThumbUrl || '',
    faceMarkers: Array.isArray(row.faceMarkers) ? row.faceMarkers : [],
  }
}

export async function logoutSession() {
  if (getToken()) {
    try {
      await apiFetch('/logout', { method: 'POST', body: {} })
    } catch (error) {
      // ignore network errors on logout
    }
  }
  setToken(null)
  localStorage.removeItem('fototeek_user')
}
