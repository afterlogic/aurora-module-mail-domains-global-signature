<template>
  <q-scroll-area class="full-height full-width">
    <div class="q-pa-lg">
      <div class="row q-mb-md">
        <div class="col text-h5">{{$t('MAILDOMAINSGLOBALSIGNATURE.HEADING_USER_GLOBAL_SIGNATURE_SETTINGS') }}</div>
      </div>
      <q-card flat bordered class="card-edit-settings">
        <q-card-section v-if="hasGlobalSignature">
          <div class="row q-mb-md">
            <div class="col-6">
              <q-checkbox dense v-model="useGlobalSignature">
                <q-item-label v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_USE_GLOBAL_SIGNATURE'"/>
              </q-checkbox>
            </div>
          </div>
          <div class="row q-mb-md">
            <div class="col-6">
              <q-checkbox dense v-model="allowCreatingIdentities">
                <q-item-label v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_ALLOW_CREATING_IDENTITIES'"/>
              </q-checkbox>
            </div>
          </div>
          <div class="row q-mb-md">
            <div class="col-2">
              <div class="q-my-sm">
                {{ $t('MAILDOMAINSGLOBALSIGNATURE.LABEL_NAME') }}
              </div>
            </div>
            <div class="col-4">
              <q-input outlined dense class="col-4" bg-color="white" v-model="name"/>
            </div>
          </div>
          <div class="row q-mb-md">
            <div class="col-2">
              <div class="q-my-sm">
                {{ $t('MAILDOMAINSGLOBALSIGNATURE.LABEL_POSITION') }}
              </div>
            </div>
            <div class="col-4">
              <q-input outlined dense class="col-4" bg-color="white" v-model="position"/>
            </div>
          </div>
          <div class="row q-mb-md">
            <div class="col-2">
              <div class="q-my-sm">
                {{ $t('MAILDOMAINSGLOBALSIGNATURE.LABEL_PHONE') }}
              </div>
            </div>
            <div class="col-4">
              <q-input outlined dense class="col-4" bg-color="white" v-model="phone"/>
            </div>
          </div>
          <div class="row q-mb-md">
            <div class="col-2">
              <div class="q-my-sm">
                {{ $t('MAILDOMAINSGLOBALSIGNATURE.LABEL_EMAIL') }}
              </div>
            </div>
            <div class="col-4">
              <q-input outlined dense class="col-4" bg-color="white" v-model="email"/>
            </div>
          </div>
          <div class="row">
            <div class="col-2">
              <div class="q-my-sm">
                {{ $t('MAILDOMAINSGLOBALSIGNATURE.LABEL_OPTIONAL') }}
              </div>
            </div>
            <div class="col-4">
              <q-input outlined dense class="col-4" bg-color="white" v-model="optional"/>
            </div>
          </div>
        </q-card-section>
        <q-card-section v-else>
          <div class="row">
            <div class="col-10">
              <q-item-label caption v-t="'MAILDOMAINSGLOBALSIGNATURE.HINT_SIGNATURE_NOT_SET'"/>
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
  name: 'MailGlobalSignatureAdminSettingsPerUser',

  data () {
    return {
      user: null,
      hasGlobalSignature: false,
      useGlobalSignature: false,
      allowCreatingIdentities: false,
      name: '',
      position: '',
      phone: '',
      email: '',
      optional: '',
      loading: false,
      saving: false,
    }
  },

  computed: {
    currentTenantId () {
      return this.$store.getters['tenants/getCurrentTenantId']
    },

    allDomainLists () {
      return this.$store.getters['maildomains/getDomains']
    },

    domains () {
      return typesUtils.pArray(this.allDomainLists[this.currentTenantId])
    }
  },

  watch: {
    $route(to, from) {
      this.parseRoute()
    },
  },

  mounted () {
    this.$store.dispatch('maildomains/requestDomainsIfNecessary', {
      tenantId: this.currentTenantId
    })
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
        useGlobalSignature = _.isFunction(this.user?.getData) ? !!this.user?.getData('MailDomainsGlobalSignature::UseGlobalSignature') : '',
        allowCreatingIdentities = _.isFunction(this.user?.getData) ? !!this.user?.getData('MailDomainsGlobalSignature::AllowCreatingIdentities') : '',
        name = _.isFunction(this.user?.getData) ? this.user?.getData('MailDomainsGlobalSignature::Name') : '',
        position = _.isFunction(this.user?.getData) ? this.user?.getData('MailDomainsGlobalSignature::Position') : '',
        phone = _.isFunction(this.user?.getData) ? this.user?.getData('MailDomainsGlobalSignature::Phone') : '',
        email = _.isFunction(this.user?.getData) ? this.user?.getData('MailDomainsGlobalSignature::Email') : '',
        optional = _.isFunction(this.user?.getData) ? this.user?.getData('MailDomainsGlobalSignature::Optional') : ''
      return this.useGlobalSignature !== useGlobalSignature || this.allowCreatingIdentities !== allowCreatingIdentities ||
             this.name !== name || this.position !== position || this.phone !== phone || this.email !== email ||
             this.optional !== optional
    },

    /**
     * Method is used in doBeforeRouteLeave mixin,
     * do not use async methods - just simple and plain reverting of values
     * !! hasChanges method must return true after executing revertChanges method
     */
    revertChanges () {
      this.populateUserData()
    },

    populateUserData () {
      if (_.isFunction(this.user?.getData)) {
        const
          domainId = this.user?.getData('MailDomains::DomainId'),
          domain = this.domains.find(domain => domain.id === domainId),
          signatureId = typesUtils.pInt(domain && domain.data && domain.data['MailDomainsGlobalSignature::SignatureId'])
        this.hasGlobalSignature = signatureId > 0
        this.useGlobalSignature = !!this.user?.getData('MailDomainsGlobalSignature::UseGlobalSignature')
        this.allowCreatingIdentities = !!this.user?.getData('MailDomainsGlobalSignature::AllowCreatingIdentities')
        this.name = this.user?.getData('MailDomainsGlobalSignature::Name')
        this.position = this.user?.getData('MailDomainsGlobalSignature::Position')
        this.phone = this.user?.getData('MailDomainsGlobalSignature::Phone')
        this.email = this.user?.getData('MailDomainsGlobalSignature::Email')
        this.optional = this.user?.getData('MailDomainsGlobalSignature::Optional')
      } else {
        this.hasGlobalSignature = false
        this.useGlobalSignature = false
        this.allowCreatingIdentities = false
        this.name = ''
        this.position = ''
        this.phone = ''
        this.email = ''
        this.optional = ''
      }
    },

    parseRoute () {
      const userId = typesUtils.pPositiveInt(this.$route?.params?.id)
      if (this.user?.id !== userId) {
        this.user = {
          id: userId,
        }
        this.populate()
      }
    },

    populate () {
      this.loading = true
      const currentTenantId = this.$store.getters['tenants/getCurrentTenantId']
      cache.getUser(currentTenantId, this.user.id).then(({ user, userId }) => {
        if (userId === this.user.id) {
          this.loading = false
          if (user && _.isFunction(user?.getData)) {
            this.user = user
            this.populateUserData()
          } else {
            this.$emit('no-user-found')
          }
        }
      })
    },

    updateSettingsForEntity () {
      if (!this.saving) {
        this.saving = true
        const parameters = {
          UserId: this.user?.id,
          TenantId: this.user.tenantId,
          UseGlobalSignature: this.useGlobalSignature,
          AllowCreatingIdentities: this.allowCreatingIdentities,
          Name: this.name,
          Position: this.position,
          Phone: this.phone,
          Email: this.email,
          Optional: this.optional,
        }
        webApi.sendRequest({
          moduleName: 'MailDomainsGlobalSignature',
          methodName: 'UpdateGlobalSignatureUserData',
          parameters
        }).then(result => {
          this.saving = false
          if (result) {
            cache.getUser(parameters.TenantId, parameters.UserId).then(({ user }) => {
              user.updateData([
                {
                  field: 'MailDomainsGlobalSignature::UseGlobalSignature',
                  value: parameters.UseGlobalSignature
                },
                {
                  field: 'MailDomainsGlobalSignature::AllowCreatingIdentities',
                  value: parameters.AllowCreatingIdentities
                },
                {
                  field: 'MailDomainsGlobalSignature::Name',
                  value: parameters.Name
                },
                {
                  field: 'MailDomainsGlobalSignature::Position',
                  value: parameters.Position
                },
                {
                  field: 'MailDomainsGlobalSignature::Phone',
                  value: parameters.Phone
                },
                {
                  field: 'MailDomainsGlobalSignature::Email',
                  value: parameters.Email
                },
                {
                  field: 'MailDomainsGlobalSignature::Optional',
                  value: parameters.Optional
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
