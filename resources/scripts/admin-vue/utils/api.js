import axios from 'axios'

const axiosAnymarket = axios.create({
  baseURL: wpApiSettings.root + 'anymarket/v1',
  headers: {
    'X-WP-Nonce': wpApiSettings.nonce,
  },
})

export const anymarket = {
  get(endpoint) {
    return axiosAnymarket.get(endpoint)
  },

  put(endpoint, body) {
    return axiosAnymarket.put(endpoint, body)
  },

  post(endpoint, body) {
    return axiosAnymarket.post(endpoint, body)
  },
}

const axiosWoo = axios.create({
  baseURL: wpApiSettings.root + 'wc/v3',
  headers: {
    'X-WP-Nonce': wpApiSettings.nonce,
  },
})

export const wooApi = {
  get(endpoint, body) {
    return axiosWoo.get(endpoint, body)
  },

  put(endpoint, body) {
    return axiosWoo.put(endpoint, body)
  },
}
