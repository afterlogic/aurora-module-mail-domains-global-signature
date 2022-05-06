<template>
  <q-scroll-area class="full-height full-width">
    <div class="q-pa-lg">
      <div class="row q-mb-md">
        <div class="col text-h5">{{$t('MAILDOMAINSGLOBALSIGNATURE.HEADING_DOMAIN_GLOBAL_SIGNATURE_SETTINGS') }}</div>
      </div>
      <q-card flat bordered class="card-edit-settings">
        <q-card-section>
          <div class="row">
            <div class="col-2">
              <div class="q-my-sm">
                {{ $t('MAILDOMAINSGLOBALSIGNATURE.LABEL_SIGNATURE') }}
              </div>
            </div>
            <div class="col-4">
              <q-select outlined dense bg-color="white" v-model="selectedSignatureId"
                        emit-value map-options :options="signatureOptions" option-label="name" />
            </div>
          </div>
        </q-card-section>
      </q-card>
      <div class="q-pt-md text-right">
        <q-btn unelevated no-caps dense class="q-px-sm" :ripple="false" color="primary"
               :label="$t('COREWEBCLIENT.ACTION_SAVE')" @click="saveSignature"/>
      </div>
    </div>
    <q-inner-loading style="justify-content: flex-start;" :showing="loading || saving">
      <q-linear-progress query />
    </q-inner-loading>
  </q-scroll-area>
</template>

<script>
import _ from 'lodash'

import errors from 'src/utils/errors'
import notification from 'src/utils/notification'
import typesUtils from 'src/utils/types'
import webApi from 'src/utils/web-api'

export default {
  name: 'MailGlobalSignatureAdminSettingsPerDomain',

  data () {
    return {
      domain: null,
      selectedSignatureId: 0,

      loading: false,
      saving: false,
    }
  },

  computed: {
    currentTenantId() {
      return this.$store.getters['tenants/getCurrentTenantId']
    },

    globalSignatures() {
      return this.$store.getters['maildomainsglobalsignature/getSignatures']
    },

    signatureOptions() {
      const signatureOptions = this.globalSignatures.map(signature => {
        return { name: signature.name, value: signature.id }
      })
      signatureOptions.unshift({ name: this.$t('MAILDOMAINSGLOBALSIGNATURE.LABEL_SIGNATURE_NOT_SELECTED'), value: 0 })
      return signatureOptions
    },
  },

  watch: {
    $route(to, from) {
      this.parseRoute()
    },
  },

  mounted() {
    this.$store.dispatch('maildomainsglobalsignature/requestSignaturesIfNecessary')
    this.parseRoute()
  },

  beforeRouteLeave (to, from, next) {
    this.doBeforeRouteLeave(to, from, next)
  },

  methods: {
    /**
     * Method is used in doBeforeRouteLeave mixin
     */
    hasChanges () {
      const selectedSignatureId = typesUtils.pInt(this.domain?.data['MailDomainsGlobalSignature::SignatureId'])
      return this.selectedSignatureId !== selectedSignatureId
    },

    /**
     * Method is used in doBeforeRouteLeave mixin,
     * do not use async methods - just simple and plain reverting of values
     * !! hasChanges method must return true after executing revertChanges method
     */
    revertChanges () {
      this.populateDomainData()
    },

    populateDomainData () {
      this.selectedSignatureId = typesUtils.pInt(this.domain?.data['MailDomainsGlobalSignature::SignatureId'])
    },

    parseRoute () {
      const domainId = typesUtils.pPositiveInt(this.$route?.params?.id)
      this.domain = this.$store.getters['maildomains/getDomain'](this.currentTenantId, domainId)
      this.populateDomainData()
    },

    saveSignature () {
      if (!this.saving && this.domain) {
        this.saving = true
        const
          addSignature = this.selectedSignatureId > 0,
          methodName = addSignature ? 'AddGlobalSignatureToDomain' : 'RemoveGlobalSignatureFromDomain',
          parameters = {
            DomainId: this.domain.id
          }
        if (addSignature) {
          parameters.SignatureId = this.selectedSignatureId
        }
        webApi.sendRequest({
          moduleName: 'MailDomainsGlobalSignature',
          methodName,
          parameters
        }).then(result => {
          this.saving = false
          if (result && this.domain && this.domain.id === parameters.DomainId) {
            this.$store.dispatch('maildomains/setDomainData', {
              tenantId: this.domain.tenantId,
              domainId: this.domain.id,
              domainData: {
                'MailDomainsGlobalSignature::SignatureId': parameters.SignatureId
              }
            })
            notification.showReport(this.$t('COREWEBCLIENT.REPORT_SETTINGS_UPDATE_SUCCESS'))
          } else {
            notification.showError(this.$t('COREWEBCLIENT.ERROR_SAVING_SETTINGS_FAILED'))
          }
        }, response => {
          this.saving = false
          notification.showError(errors.getTextFromResponse(response, this.$t('COREWEBCLIENT.ERROR_SAVING_SETTINGS_FAILED')))
        })
      }
    },
  }
}
</script>
