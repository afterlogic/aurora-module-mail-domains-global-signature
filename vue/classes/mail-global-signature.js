import typesUtils from 'src/utils/types'

class MailGlobalSignature {
  constructor(signatureData) {
    this.id = typesUtils.pInt(signatureData?.Id)
    this.name = typesUtils.pString(signatureData?.Name)
    this.html = typesUtils.pString(signatureData?.Signature)
  }

  update (signatureName, signatureHtml) {
    this.name = signatureName
    this.html = signatureHtml
  }
}

export default MailGlobalSignature
