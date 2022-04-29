import typesUtils from 'src/utils/types'

class MailDomainsGlobalSignatureSettings {
  constructor(appData) {
    const mailDomainsGlobalSignatureData = typesUtils.pObject(appData.MailDomainsGlobalSignature)
    console.log({ mailDomainsGlobalSignatureData })
  }
}

let settings = null

export default {
  init (appData) {
    settings = new MailDomainsGlobalSignatureSettings(appData)
  },
}
