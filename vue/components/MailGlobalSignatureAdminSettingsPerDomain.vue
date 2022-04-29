<template>
  <q-scroll-area class="full-height full-width">
    <div class="q-pa-lg">
      <div class="row q-mb-md">
        <div class="col text-h5">{{$t('MAILDOMAINSGLOBALSIGNATURE.HEADING_DOMAIN_GLOBAL_SIGNATURE_SETTINGS') }}</div>
      </div>
      <q-card flat bordered class="card-edit-settings">
        <q-card-section>
          <div class="row q-mb-md">
            <div class="col-2">
              <div class="q-my-sm">
                {{ $t('MAILDOMAINSGLOBALSIGNATURE.LABEL_SIGNATURE') }}
              </div>
            </div>
            <div class="col-4">
              <q-select outlined dense bg-color="white" v-model="selectedSignature"
                        emit-value map-options :options="signatureOptions" option-label="name" />
            </div>
          </div>
        </q-card-section>
      </q-card>
      <div class="q-pt-md text-right">
        <q-btn unelevated no-caps dense class="q-px-sm" :ripple="false" color="primary"
               :label="$t('COREWEBCLIENT.ACTION_SAVE')" @click="updateSettingsForEntity"/>
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

import cache from 'src/cache'

export default {
  name: 'MailGlobalSignatureAdminSettingsPerDomain',

  data () {
    return {
      selectedSignature: 0,
      signatureOptions: [
        { name: this.$t('MAILDOMAINSGLOBALSIGNATURE.LABEL_SIGNATURE_NOT_SELECTED'), value: 0 }
      ],

      domain: null,
      name: '',
      position: '',
      phone: '',
      email: '',
      loading: false,
      saving: false,
    }
  },

  computed: {
    currentTenantId() {
      return this.$store.getters['tenants/getCurrentTenantId']
    },
  },

  watch: {
    $route(to, from) {
      this.parseRoute()
    },
  },

  mounted() {
    this.$store.dispatch('tenants/completeTenantData', this.tenant.id)
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
      const
        name = _.isFunction(this.domain?.getData) ? this.domain?.getData('MailDomainsGlobalSignature::Name') : '',
        position = _.isFunction(this.domain?.getData) ? this.domain?.getData('MailDomainsGlobalSignature::Position') : '',
        phone = _.isFunction(this.domain?.getData) ? this.domain?.getData('MailDomainsGlobalSignature::Phone') : '',
        email = _.isFunction(this.domain?.getData) ? this.domain?.getData('MailDomainsGlobalSignature::Email') : ''
      return this.name !== name || this.position !== position || this.phone !== phone || this.email !== email
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
      if (_.isFunction(this.domain?.getData)) {
        this.name = this.domain?.getData('MailDomainsGlobalSignature::Name')
        this.position = this.domain?.getData('MailDomainsGlobalSignature::Position')
        this.phone = this.domain?.getData('MailDomainsGlobalSignature::Phone')
        this.email = this.domain?.getData('MailDomainsGlobalSignature::Email')
      } else {
        this.name = ''
        this.position = ''
        this.phone = ''
        this.email = ''
      }
    },

    parseRoute () {
      const domainId = typesUtils.pPositiveInt(this.$route?.params?.id)
      const domain = this.$store.getters['maildomains/getDomain'](this.currentTenantId, domainId)
      // if (domain) {
      //   this.fillUp(domain)
      // } else {
      //   this.$emit('no-domain-found')
      // }
      // if (this.domain?.id !== domainId) {
      //   this.domain = {
      //     id: domainId,
      //   }
      //   this.populate()
      // }
    },

    populate () {
      this.loading = true
      const currentTenantId = this.$store.getters['tenants/getCurrentTenantId']
      cache.getDomain(currentTenantId, this.domain.id).then(({ domain, domainId }) => {
        if (domainId === this.domain.id) {
          this.loading = false
          if (domain && _.isFunction(domain?.getData)) {
            this.domain = domain
            this.populateDomainData()
          } else {
            this.$emit('no-domain-found')
          }
        }
      })
    },

    updateSettingsForEntity () {
      if (!this.saving) {
        this.saving = true
        const parameters = {
          DomainId: this.domain?.id,
          TenantId: this.domain.tenantId,
          Name: typesUtils.pInt(this.name),
          Position: typesUtils.pInt(this.position),
          Phone: typesUtils.pInt(this.phone),
          Email: typesUtils.pInt(this.email),
        }
        webApi.sendRequest({
          moduleName: 'MailDomainsGlobalSignature',
          methodName: 'UpdateDomain',
          parameters
        }).then(result => {
          this.saving = false
          if (result) {
            cache.getDomain(parameters.TenantId, parameters.EntityId).then(({ domain }) => {
              domain.updateData([
                {
                  field: 'MailDomainsGlobalSignature::Name',
                  value: parameters.name
                },
                {
                  field: 'MailDomainsGlobalSignature::Position',
                  value: parameters.position
                },
                {
                  field: 'MailDomainsGlobalSignature::Phone',
                  value: parameters.phone
                },
                {
                  field: 'MailDomainsGlobalSignature::Email',
                  value: parameters.email
                },
              ])
              this.populate()
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
