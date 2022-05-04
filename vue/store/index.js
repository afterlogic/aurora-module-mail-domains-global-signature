import Vue from 'vue'

import _ from 'lodash'

import errors from 'src/utils/errors'
import notification from 'src/utils/notification'
import typesUtils from 'src/utils/types'
import webApi from 'src/utils/web-api'

import MailGlobalSignature from '../classes/mail-global-signature'

export default {
  namespaced: true,

  state: {
    signatures: null,
  },

  mutations: {
    setSignatures(state, signatures) {
      state.signatures = signatures
    },
  },

  actions: {
    requestSignaturesIfNecessary({ state, dispatch }) {
      if (state.signatures === null) {
        dispatch('requestSignatures')
      }
    },

    requestSignatures({ commit }) {
      webApi.sendRequest({
        moduleName: 'MailDomainsGlobalSignature',
        methodName: 'GetGlobalSignatures',
      }).then(result => {
        if (_.isArray(result?.Items)) {
          const signatures = _.map(result.Items, function (data) {
            return new MailGlobalSignature(data)
          })
          commit('setSignatures', signatures)
        } else {
          commit('setSignatures', [])
        }
      }, response => {
        notification.showError(errors.getTextFromResponse(response))
        commit('setSignatures', [])
      })
    },
  },

  getters: {
    getSignatures (state) {
      return typesUtils.pArray(state.signatures)
    },

    getSignature (state) {
      return function (signatureId) {
        return state.signatures.find(signature => signature.id === signatureId)
      }
    },
  },
}
