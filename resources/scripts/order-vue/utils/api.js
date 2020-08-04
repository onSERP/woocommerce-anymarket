import axios from 'axios'

const settings = window.anymarket
const protocol = settings.sandbox ? 'http' : 'https'
const subdomain = settings.sandbox ? 'sandbox-api' : 'api'

const axiosAnymarket = axios.create({
  baseURL: `${protocol}://${subdomain}.anymarket.com.br/v2`,
  headers: {
    gumgaToken: settings.token,
  },
})

export const anymarket = {
  get(endpoint) {
    return axiosAnymarket.get(endpoint)
  },
}
