import Vue from 'vue'
import Vuetify, {
  VDataTable,
  VDataTableHeader,
  VDataFooter,
  VSimpleCheckbox,
  VEditDialog,
} from 'vuetify/lib'

Vue.use(Vuetify, {
  components: {
    VDataTable,
    VDataTableHeader,
    VDataFooter,
    VSimpleCheckbox,
    VEditDialog,
  },
})

const opts = {
  icons: {
    iconfont: 'mdiSvg',
  },
}

export default new Vuetify(opts)
