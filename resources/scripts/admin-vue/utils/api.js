import axios from 'axios'

const axiosInstance = axios.create({
  baseURL: wpApiSettings.root + 'anymarket/v1',
  headers: {
    'X-WP-Nonce': wpApiSettings.nonce,
  },
})

export const api = {
  get(endpoint) {
    return axiosInstance.get(endpoint)
  },

  put(endpoint, body) {
    return axiosInstance.put(endpoint, body)
  },
}
