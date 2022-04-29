import { i18n } from 'src/boot/i18n'

import _ from 'lodash'

import errors from 'src/utils/errors'
import notification from 'src/utils/notification'
import typesUtils from 'src/utils/types'
import webApi from 'src/utils/web-api'

import MailGlobalSignature from './classes/mail-global-signature'

export default {
  getSignatures (search, page, limit) {
    return new Promise((resolve, reject) => {
      webApi.sendRequest({
        moduleName: 'MailDomainsGlobalSignature',
        methodName: 'GetGlobalSignatures',
        parameters: {
          Search: search,
          Offset: limit * (page - 1),
          Limit: limit,
        },
      }).then(result => {
        if (_.isArray(result?.Items)) {
          const
            signatures = result.Items.map(signatureData => new MailGlobalSignature(signatureData)),
            totalCount = typesUtils.pInt(result.Count)
          resolve({ signatures, totalCount, search, page, limit })
        } else {
          resolve({ signatures: [], totalCount: 0, search, page, limit })
        }
      }, response => {
        notification.showError(errors.getTextFromResponse(response))
        resolve({ signatures: [], totalCount: 0, search, page, limit })
      })
    })
  },

  addGlobalSignature (signatureName, signatureHtml) {
    return new Promise((resolve, reject) => {
      const parameters = {
        Name: signatureName,
        Signature: signatureHtml,
      }
      webApi.sendRequest({
        moduleName: 'MailDomainsGlobalSignature',
        methodName: 'AddGlobalSignature',
        parameters,
      }).then(result => {
        if (_.isSafeInteger(result)) {
          notification.showReport(i18n.tc('COREWEBCLIENT.REPORT_SETTINGS_UPDATE_SUCCESS'))
          resolve({ newSignatureId: result })
        } else {
          notification.showError(i18n.tc('COREWEBCLIENT.ERROR_SAVING_SETTINGS_FAILED'))
          resolve({ newSignatureId: 0 })
        }
      }, error => {
        notification.showError(errors.getTextFromResponse(error, i18n.tc('COREWEBCLIENT.ERROR_SAVING_SETTINGS_FAILED')))
        resolve({ newSignatureId: 0 })
      })
    })
  },

  updateGlobalSignature (signatureId, signatureName, signatureHtml) {
    return new Promise((resolve, reject) => {
      const parameters = {
        SignatureId: signatureId,
        Name: signatureName,
        Signature: signatureHtml,
      }
      webApi.sendRequest({
        moduleName: 'MailDomainsGlobalSignature',
        methodName: 'UpdateGlobalSignature',
        parameters,
      }).then(result => {
        if (result === true) {
          notification.showReport(i18n.tc('COREWEBCLIENT.REPORT_SETTINGS_UPDATE_SUCCESS'))
          resolve({ isUpdated: true })
        } else {
          notification.showError(i18n.tc('COREWEBCLIENT.ERROR_SAVING_SETTINGS_FAILED'))
          resolve({ isUpdated: false })
        }
      }, error => {
        notification.showError(errors.getTextFromResponse(error, i18n.tc('COREWEBCLIENT.ERROR_SAVING_SETTINGS_FAILED')))
        resolve({ isUpdated: false })
      })
    })
  },

  deleteGlobalSignature (signatureId) {
    return new Promise((resolve, reject) => {
      webApi.sendRequest({
        moduleName: 'MailDomainsGlobalSignature',
        methodName: 'DeleteGlobalSignature',
        parameters: {
          SignatureId: signatureId,
        },
      }).then(result => {
        if (result === true) {
          resolve({ isDeleted: true })
        } else {
          notification.showError(i18n.tc('MAILDOMAINSGLOBALSIGNATURE.ERROR_DELETE_GLOBAL_SIGNATURE'))
          resolve({ isDeleted: false })
        }
      }, error => {
        notification.showError(errors.getTextFromResponse(error, i18n.tc('MAILDOMAINSGLOBALSIGNATURE.ERROR_DELETE_GLOBAL_SIGNATURE')))
        resolve({ isDeleted: false })
      })
    })
  },
}
