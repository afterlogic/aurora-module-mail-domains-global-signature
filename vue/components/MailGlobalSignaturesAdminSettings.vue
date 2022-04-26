<template>
  <div class="full-height full-width">
    <q-scroll-area class="full-height full-width">
      <div class="q-pa-lg ">
        <div class="row q-mb-md">
          <div class="col text-h5" v-t="'MAILDOMAINSGLOBALSIGNATURE.HEADING_ALL_GLOBAL_SIGNATURES_SETTINGS'"></div>
          <div class="col text-right" v-if="!createMode">
            <q-btn unelevated no-caps dense class="q-px-sm" :ripple="false" color="primary" @click="addNewSignature"
                   :label="$t('MAILDOMAINSGLOBALSIGNATURE.ACTION_ADD_NEW_GLOBAL_SIGNATURE')" />
          </div>
        </div>
        <div class="relative-position" v-if="!createMode">
          <q-list dense bordered separator class="rounded-borders q-mb-md" style="overflow: hidden"
                  v-if="signatures.length > 0">
            <q-item clickable :class="currentSignatureId === signature.id ? 'bg-grey-4' : 'bg-white'"
                    v-for="signature in signatures" :key="signature.id" @click="route(signature.id)">
              <q-item-section>
                <q-item-label>
                  {{ signature.name }}
                  <span v-if="signature.tenantName" class="text-grey-6">{{ $t('MAILDOMAINSGLOBALSIGNATURE.LABEL_HINT_SERVERS_TENANTNAME', { TENANTNAME: signature.tenantName }) }}</span>
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-btn dense flat no-caps color="negative" class="no-hover" :label="$t('COREWEBCLIENT.ACTION_DELETE')"
                       @click.native.stop="askDeleteSignature(signature.name, signature.id, signature.tenantId)"/>
              </q-item-section>
            </q-item>
          </q-list>
          <div class="flex flex-left q-mb-lg" v-if="showSearch || showPagination">
            <q-input rounded outlined dense bg-color="white" v-model="enteredSearch" v-if="showSearch" @keyup.enter="route">
              <template v-slot:append>
                <q-btn dense flat :ripple="false" icon="search" @click="route" />
              </template>
            </q-input>
            <q-pagination flat active-color="primary" color="grey-6" v-model="selectedPage" :max="pagesCount" v-if="showPagination" />
          </div>
          <div v-if="signatures.length === 0 && loadingSignatures" style="height: 150px;"></div>
          <q-card flat bordered class="card-edit-settings"
                  v-if="signatures.length === 0 && !loadingSignatures && search === ''">
            <q-card-section class="text-caption" v-t="'MAILDOMAINSGLOBALSIGNATURE.INFO_NO_SERVERS'" />
          </q-card>
          <q-card flat bordered class="card-edit-settings"
                  v-if="signatures.length === 0 && !loadingSignatures && search !== ''">
            <q-card-section class="text-caption" v-t="'MAILDOMAINSGLOBALSIGNATURE.INFO_NO_SERVERS_FOUND'" />
          </q-card>
        </div>

        <q-card flat bordered class="card-edit-settings" v-if="showSignatureFields || createMode">
          <q-card-section>
            <div class="row q-mb-md" v-if="createMode && tenantOptions.length > 1">
              <div class="col-2 q-my-sm q-pl-sm required-field" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_TENANT'"></div>
              <div class="col-5">
                <q-select outlined dense bg-color="white" v-model="selectedTenantId"
                          emit-value map-options :options="tenantOptions" />
              </div>
            </div>
            <div class="row">
              <div class="col-2 q-my-sm q-pl-sm required-field" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_DISPLAY_NAME'"></div>
              <div class="col-5">
                <q-input outlined dense bg-color="white" v-model="signatureName" ref="signatureName"></q-input>
              </div>
            </div>
            <div class="row q-mt-sm q-mb-lg">
              <div class="col-2"></div>
              <div class="col-9">
                <q-item-label caption v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_HINT_DISPLAY_NAME'" />
              </div>
            </div>

            <div class="row q-mb-md">
              <div class="col-2 q-my-sm q-pl-sm required-field" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_IMAP_SERVER'"></div>
              <div class="col-3">
                <q-input outlined dense bg-color="white" v-model="imapSignature" ref="imapSignature"
                         @blur="fillUpSmtpSignatureFromImapSignature"></q-input>
              </div>
              <div class="col-1 q-my-sm text-right q-pr-md  q-pl-sm required-field" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_PORT'"></div>
              <div class="col-1">
                <q-input outlined dense bg-color="white" v-model="imapPort" ref="imapPort"></q-input>
              </div>
              <div class="col-1 q-my-sm q-pl-md">
                <q-checkbox dense v-model="imapSsl" label="SSL" />
              </div>
            </div>

            <div class="row q-mb-md">
              <div class="col-2 q-my-sm q-pl-sm required-field" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_SMTP_SERVER'"></div>
              <div class="col-3">
                <q-input outlined dense bg-color="white" v-model="smtpSignature" ref="smtpSignature"></q-input>
              </div>
              <div class="col-1 q-my-sm text-right q-pr-md q-pl-sm required-field" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_PORT'"></div>
              <div class="col-1">
                <q-input outlined dense bg-color="white" v-model="smtpPort" ref="smtpPort"></q-input>
              </div>
              <div class="col-1 q-my-sm q-pl-md">
                <q-checkbox dense v-model="smtpSsl" :label="$t('MAILDOMAINSGLOBALSIGNATURE.LABEL_SSL')" />
              </div>
            </div>
            <div class="row q-mb-md">
              <div class="col-2"></div>
              <div class="col-6">
                <q-item-label v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_SMTP_AUTHENTICATION'" />
                <q-list dense >
                  <q-item manual-focus>
                    <q-item-section class="q-pr-none">
                      <span>
                      <q-radio dense v-model="smtpAuthentication" :val="smtpAuthTypeEnum.NoAuthentication" :label="$t('MAILDOMAINSGLOBALSIGNATURE.LABEL_NO_AUTHENTICATION')" />
                      </span>
                    </q-item-section>
                  </q-item>
                  <q-item manual-focus>
                    <q-item-section avatar>
                      <span>
                        <q-radio dense v-model="smtpAuthentication" :val="smtpAuthTypeEnum.UseSpecifiedCredentials" :label="$t('MAILDOMAINSGLOBALSIGNATURE.LABEL_USE_SPECIFIED_CREDENTIALS')" />
                      </span>
                    </q-item-section>
                    <q-item-section>
                      <q-input outlined dense bg-color="white" v-model="smtpLogin" :placeholder="$t('COREWEBCLIENT.LABEL_LOGIN')"></q-input>
                    </q-item-section>
                    <q-item-section>
                      <q-input outlined dense bg-color="white" type="password" autocomplete="new-password"
                               v-model="smtpPassword" :placeholder="$t('COREWEBCLIENT.LABEL_PASSWORD')" />
                    </q-item-section>
                  </q-item>
                  <q-item manual-focus>
                    <q-item-section>
                      <span>
                      <q-radio dense v-model="smtpAuthentication" :val="smtpAuthTypeEnum.UseUserCredentials"
                               :label="$t('MAILDOMAINSGLOBALSIGNATURE.LABEL_USE_USER_CREDENTIALS')"/>
                      </span>
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>
            </div>
            <div class="row q-mb-md">
              <div class="col-2"></div>
              <div class="col-5">
                <q-checkbox dense v-model="enableSieve" :label="$t('MAILDOMAINSGLOBALSIGNATURE.LABEL_ENABLE_SIEVE')" />
              </div>
            </div>
            <div class="row q-mb-md">
              <div class="col-2 q-my-sm" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_SIEVE_PORT'"></div>
              <div class="col-5">
                <q-input outlined dense bg-color="white" v-model="sievePort"></q-input>
              </div>
            </div>
            <div class="row q-mb-md">
              <div class="col-2"></div>
              <div class="col-5">
                <q-checkbox dense v-model="useThreading" :label="$t('MAILDOMAINSGLOBALSIGNATURE.LABEL_USE_THREADING')" />
              </div>
            </div>
            <div class="row q-mb-sm">
              <div class="col-2"></div>
              <div class="col-5">
                <q-checkbox dense v-model="useFullEmail"
                            :label="$t('MAILDOMAINSGLOBALSIGNATURE.LABEL_USE_FULL_EMAIL_ADDRESS_AS_LOGIN')" />
              </div>
            </div>
            <div class="row">
              <div class="col-2"></div>
              <div class="col-5">
                <q-item-label caption v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_HINT_USE_FULL_EMAIL_ADDRESS_AS_LOGIN'" />
              </div>
            </div>
          </q-card-section>
        </q-card>

        <q-card flat bordered class="card-edit-settings q-mt-md" v-if="showSignatureFields || createMode">
          <q-card-section>
            <div class="row q-mb-sm">
              <div class="col-10">
                <q-checkbox dense v-model="setExternalAccessSignatures"
                            :label="$t('MAILDOMAINSGLOBALSIGNATURE.LABEL_ADMIN_EXTERNAL_ACCESS_SERVERS')" />
              </div>
            </div>
            <div class="row q-mb-md">
              <div class="col-10">
                <q-item-label caption v-html="$t('MAILDOMAINSGLOBALSIGNATURE.LABEL_HINT_ADMIN_EXTERNAL_ACCESS_SERVERS')" />
              </div>
            </div>
            <div class="row q-mb-md">
              <div class="col-2 q-my-sm" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_IMAP_SERVER'"
                   :class="setExternalAccessSignatures ? '' : 'disabled'"></div>
              <div class="col-3">
                <q-input outlined dense bg-color="white" v-model="externalAccessImapSignature"
                         :disable="!setExternalAccessSignatures"></q-input>
              </div>
              <div class="col-1 q-my-sm text-right q-pr-md" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_PORT'"
                   :class="setExternalAccessSignatures ? '' : 'disabled'"></div>
              <div class="col-1">
                <q-input outlined dense bg-color="white" v-model="externalAccessImapPort"
                         :disable="!setExternalAccessSignatures"></q-input>
              </div>
              <div class="col-1 q-my-sm text-right q-pr-md" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_ALTERNATIVE_PORT'"
                   :class="setExternalAccessSignatures ? '' : 'disabled'"></div>
              <div class="col-1">
                <q-input outlined dense bg-color="white" v-model="externalAccessImapAlterPortModel"
                         :disable="!setExternalAccessSignatures"></q-input>
              </div>
            </div>
            <div class="row q-mb-md">
              <div class="col-2 q-my-sm" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_POP3_SERVER'"
                   :class="setExternalAccessSignatures ? '' : 'disabled'"></div>
              <div class="col-3">
                <q-input outlined dense bg-color="white" v-model="externalAccessPop3Signature"
                         :disable="!setExternalAccessSignatures"></q-input>
              </div>
              <div class="col-1 q-my-sm text-right q-pr-md" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_PORT'"
                   :class="setExternalAccessSignatures ? '' : 'disabled'"></div>
              <div class="col-1">
                <q-input outlined dense bg-color="white" v-model="externalAccessPop3Port"
                         :disable="!setExternalAccessSignatures"></q-input>
              </div>
              <div class="col-1 q-my-sm text-right q-pr-md" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_ALTERNATIVE_PORT'"
                   :class="setExternalAccessSignatures ? '' : 'disabled'"></div>
              <div class="col-1">
                <q-input outlined dense bg-color="white" v-model="externalAccessPop3AlterPortModel"
                         :disable="!setExternalAccessSignatures"></q-input>
              </div>
            </div>
            <div class="row">
              <div class="col-2 q-my-sm" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_SMTP_SERVER'"
                   :class="setExternalAccessSignatures ? '' : 'disabled'"></div>
              <div class="col-3">
                <q-input outlined dense bg-color="white" v-model="externalAccessSmtpSignature"
                         :disable="!setExternalAccessSignatures"></q-input>
              </div>
              <div class="col-1 q-my-sm text-right q-pr-md" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_PORT'"
                   :class="setExternalAccessSignatures ? '' : 'disabled'"></div>
              <div class="col-1">
                <q-input outlined dense bg-color="white" v-model="externalAccessSmtpPort"
                         :disable="!setExternalAccessSignatures"></q-input>
              </div>
              <div class="col-1 q-my-sm text-right q-pr-md" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_ALTERNATIVE_PORT'"
                   :class="setExternalAccessSignatures ? '' : 'disabled'"></div>
              <div class="col-1">
                <q-input outlined dense bg-color="white" v-model="externalAccessSmtpAlterPortModel"
                         :disable="!setExternalAccessSignatures"></q-input>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <q-card flat bordered class="card-edit-settings q-mt-md" v-if="oauthConnectorsData.length > 0 && (showSignatureFields || createMode)">
          <q-card-section>
            <div class="row">
              <div class="col-6">
                <q-item-label v-t="'MAILDOMAINSGLOBALSIGNATURE.INFO_ADMIN_OAUTH'" />
                <q-list dense>
                  <q-item manual-focus v-for="data in oauthConnectorsData" :key="data.type">
                    <q-item-section class="q-pr-none">
                      <span>
                        <q-radio dense v-model="oauthConnector" :val="data.type" :label="data.name" />
                      </span>
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <div class="q-pt-md text-right" v-if="showSignatureFields || createMode">
          <q-btn unelevated no-caps dense class="q-px-sm" :ripple="false" color="primary" @click="save" v-if="!createMode"
                 :label="$t('COREWEBCLIENT.ACTION_SAVE')">
          </q-btn>
          <q-btn unelevated no-caps dense class="q-px-sm" :ripple="false" color="primary" @click="create" v-if="createMode"
                 :label="$t('COREWEBCLIENT.ACTION_CREATE')">
          </q-btn>
          <q-btn unelevated no-caps dense class="q-px-sm q-ml-sm" :ripple="false" color="secondary" @click="cancelCreate"
                 v-if="createMode" :label="$t('COREWEBCLIENT.ACTION_CANCEL')">
          </q-btn>
        </div>
      </div>
      <ConfirmDialog ref="confirmDialog" />
    </q-scroll-area>
    <q-inner-loading style="justify-content: flex-start;" :showing="loadingSignatures || saving || creating">
      <q-linear-progress query />
    </q-inner-loading>
  </div>
</template>

<script>
import _ from 'lodash'

import errors from 'src/utils/errors'
import notification from 'src/utils/notification'
import typesUtils from 'src/utils/types'
import webApi from 'src/utils/web-api'

import settings from '../settings'
import cache from '../cache'

import ConfirmDialog from 'src/components/ConfirmDialog'

export default {
  name: 'MailGlobalSignaturesAdminSettings',

  components: {
    ConfirmDialog,
  },

  data() {
    return {
      smtpAuthTypeEnum: settings.getSmtpAuthTypeEnum(),

      page: 1,
      search: '',
      limit: 10,
      signatures: [],
      totalCount: 0,
      loadingSignatures: false,

      showSearch: false,
      enteredSearch: '',
      selectedPage: 1,

      currentSignatureId: 0,
      currentSignatureTenantId: 0,

      createMode: false,
      showSignatureFields: false,

      selectedTenantId: 0,
      tenantOptions: [],

      signatureName: '',
      domains: '',
      imapSignature: '',
      imapPort: 0,
      imapSsl: false,
      smtpSignature: '',
      smtpPort: 0,
      smtpSsl: false,
      smtpAuthentication: '0',
      smtpLogin: '',
      smtpPassword: '',
      enableSieve: false,
      sievePort: 0,
      useThreading: false,
      useFullEmail: false,

      setExternalAccessSignatures: false,
      externalAccessImapSignature: '',
      externalAccessImapPort: 143,
      externalAccessImapAlterPort: '',
      externalAccessPop3Signature: '',
      externalAccessPop3Port: 110,
      externalAccessPop3AlterPort: '',
      externalAccessSmtpSignature: '',
      externalAccessSmtpPort: 25,
      externalAccessSmtpAlterPort: '',

      oauthConnectorsData: [],
      oauthConnector: '',

      saving: false,
      creating: false,
    }
  },

  computed: {
    showPagination () {
      return this.signatures.length < this.totalCount
    },
    pagesCount () {
      return Math.ceil(this.totalCount / this.limit)
    },
    externalAccessImapAlterPortModel: {
      get () {
        return (this.externalAccessImapAlterPort === 0) ? '' : this.externalAccessImapAlterPort
      },
      set (val) {
        this.externalAccessImapAlterPort = val
      }
    },
    externalAccessPop3AlterPortModel: {
      get () {
        return (this.externalAccessPop3AlterPort === 0) ? '' : this.externalAccessPop3AlterPort
      },
      set (val) {
        this.externalAccessPop3AlterPort = val
      }
    },
    externalAccessSmtpAlterPortModel: {
      get () {
        return (this.externalAccessSmtpAlterPort === 0) ? '' : this.externalAccessSmtpAlterPort
      },
      set (val) {
        this.externalAccessSmtpAlterPort = val
      }
    },
  },

  watch: {
    $route (to, from) {
      if (this.$route.path === '/system/mail-signatures/create') {
        this.createMode = true
        this.showSignatureFields = false
        this.populateSignature()
      } else {
        this.createMode = false

        const search = typesUtils.pString(this.$route?.params?.search)
        const page = typesUtils.pPositiveInt(this.$route?.params?.page)
        if (this.search !== search || this.page !== page) {
          this.search = search
          this.enteredSearch = search
          this.page = page
          this.selectedPage = page
          this.populate()
        }

        const signatureId = typesUtils.pNonNegativeInt(this.$route?.params?.id)
        if (this.currentSignatureId !== signatureId) {
          this.currentSignatureId = signatureId
          this.populateSignature()
        }
      }
    },

    selectedPage () {
      if (this.selectedPage !== this.page) {
        this.route()
      }
    },

    imapSsl () {
      if (this.imapSsl && this.imapPort === 143) {
        this.imapPort = 993
      }
      if (!this.imapSsl && this.imapPort === 993) {
        this.imapPort = 143
      }
    },

    smtpSsl () {
      if (this.smtpSsl && this.smtpPort === 25) {
        this.smtpPort = 465
      }
      if (!this.smtpSsl && this.smtpPort === 465) {
        this.smtpPort = 25
      }
    },

    imapSignature: function (val) {
      if (!this.setExternalAccessSignatures) {
        this.externalAccessImapSignature = val
      }
    },

    imapPort: function (val) {
      if (!this.setExternalAccessSignatures) {
        this.externalAccessImapPort = val
      }
    },

    smtpSignature: function (val) {
      if (!this.setExternalAccessSignatures) {
        this.externalAccessSmtpSignature = val
      }
    },

    smtpPort: function (val) {
      if (!this.setExternalAccessSignatures) {
        this.externalAccessSmtpPort = val
      }
    },

    setExternalAccessSignatures () {
      if (!this.setExternalAccessSignatures) {
        this.externalAccessImapSignature = this.imapSignature
        this.externalAccessImapPort = this.imapPort
        this.externalAccessSmtpSignature = this.smtpSignature
        this.externalAccessSmtpPort = this.smtpPort
      }
    }
  },

  beforeRouteLeave (to, from, next) {
    this.doBeforeRouteLeave(to, from, next)
  },

  beforeRouteUpdate (to, from, next) {
    this.doBeforeRouteLeave(to, from, next)
  },

  mounted () {
    this.saving = false
    this.creating = false
    this.populate()

    const tenants = this.$store.getters['tenants/getTenants']
    const tenantOptions = [
      { label: 'system-wide', value: 0 },
    ]
    if (tenants.length > 1) {
      tenants.forEach(tenant => {
        tenantOptions.push({ label: tenant.name, value: tenant.id })
      })
    }
    this.tenantOptions = tenantOptions

    this.populateOauthConnectorsData()
  },

  methods: {
    route (signatureId = 0) {
      const searchRoute = this.enteredSearch !== '' ? ('/search/' + this.enteredSearch) : ''
      const selectedPage = (this.search !== this.enteredSearch) ? 1 : this.selectedPage
      const pageRoute = selectedPage > 1 ? ('/page/' + selectedPage) : ''
      const idRoute = signatureId > 0 ? ('/id/' + signatureId) : ''
      const path = '/system/mail-signatures' + searchRoute + pageRoute + idRoute
      if (path !== this.$route.path) {
        this.$router.push(path)
      }
    },

    populateOauthConnectorsData () {
      const params = {
        oauthConnectorsData: []
      }
      this.$eventBus.$emit('MailWebclient::GetOauthConnectorsData', params)
      this.oauthConnectorsData = _.isArray(params.oauthConnectorsData)
        ? params.oauthConnectorsData.filter(data => {
          return typesUtils.isNonEmptyString(data.name) && typesUtils.isNonEmptyString(data.type)
        })
        : []
      if (this.oauthConnectorsData.length > 0) {
        this.oauthConnectorsData.unshift({
          name: this.$t('MAILDOMAINSGLOBALSIGNATURE.LABEL_ADMIN_OAUTH_NOTHING_SELECTED'),
          type: '',
        })
      }
    },

    populate () {
      this.loadingSignatures = true
      // cache.getSignatures(this.search, this.page, this.limit).then(({ signatures, totalCount, page, search }) => {
      //   if (page === this.page && search === this.search) {
      //     this.signatures = signatures
      //     this.totalCount = totalCount
      //     if (this.search === '') {
      //       this.showSearch = totalCount > this.limit
      //     }
      //     this.loadingSignatures = false
      //     if (this.currentSignatureId !== 0) {
      //       this.populateSignature()
      //     }
      //   }
      // })
    },

    populateSignature () {
      if (this.createMode) {
        this.currentSignatureId = 0
        this.selectedTenantId = (this.tenantOptions.length > 1) ? this.tenantOptions[1].value : 0
        this.signatureName = ''
        this.domains = ''
        this.imapSignature = ''
        this.imapPort = 143
        this.imapSsl = false
        this.smtpSignature = ''
        this.smtpPort = 25
        this.smtpSsl = false
        this.smtpAuthentication = this.smtpAuthTypeEnum.UseUserCredentials
        this.smtpLogin = ''
        this.smtpPassword = ''
        this.enableSieve = false
        this.sievePort = 4190
        this.useThreading = true
        this.useFullEmail = true

        this.setExternalAccessSignatures = false
        this.externalAccessImapSignature = ''
        this.externalAccessImapPort = 143
        this.externalAccessImapAlterPort = ''
        this.externalAccessPop3Signature = ''
        this.externalAccessPop3Port = 110
        this.externalAccessPop3AlterPort = ''
        this.externalAccessSmtpSignature = ''
        this.externalAccessSmtpPort = 25
        this.externalAccessSmtpAlterPort = ''

        this.oauthConnector = ''
      } else {
        const signature = _.find(this.signatures, signature => {
          return signature.id === this.currentSignatureId
        })
        this.showSignatureFields = !!signature
        if (this.showSignatureFields) {
          this.currentSignatureTenantId = signature.tenantId
          this.signatureName = signature.name
          this.domains = signature.domains
          this.imapSignature = signature.incomingSignature
          this.imapPort = signature.incomingPort
          this.imapSsl = signature.incomingUseSsl
          this.smtpSignature = signature.outgoingSignature
          this.smtpPort = signature.outgoingPort
          this.smtpSsl = signature.outgoingUseSsl
          this.smtpAuthentication = signature.smtpAuthType
          this.smtpLogin = signature.smtpLogin
          this.smtpPassword = signature.smtpPassword
          this.enableSieve = signature.enableSieve
          this.sievePort = signature.sievePort
          this.useThreading = signature.enableThreading
          this.useFullEmail = signature.useFullEmailAddressAsLogin

          this.setExternalAccessSignatures = signature.setExternalAccessSignatures
          this.externalAccessImapSignature = signature.externalAccessImapSignature
          this.externalAccessImapPort = signature.externalAccessImapPort
          this.externalAccessImapAlterPort = signature.externalAccessImapAlterPort
          this.externalAccessPop3Signature = signature.externalAccessPop3Signature
          this.externalAccessPop3Port = signature.externalAccessPop3Port
          this.externalAccessPop3AlterPort = signature.externalAccessPop3AlterPort
          this.externalAccessSmtpSignature = signature.externalAccessSmtpSignature
          this.externalAccessSmtpPort = signature.externalAccessSmtpPort
          this.externalAccessSmtpAlterPort = signature.externalAccessSmtpAlterPort

          this.oauthConnector = signature.oauthType
        }
      }
    },

    fillUpSmtpSignatureFromImapSignature () {
      if (_.isEmpty(this.smtpSignature)) {
        this.smtpSignature = this.imapSignature
      }
    },

    /**
     * Method is used in doBeforeRouteLeave mixin
     */
    hasChanges () {
      if (this.createMode) {
        let isExternalAccessChanged = this.setExternalAccessSignatures !== false
        if (!isExternalAccessChanged && this.setExternalAccessSignatures) {
          isExternalAccessChanged = this.externalAccessImapSignature !== '' || this.externalAccessImapPort !== 143 ||
              this.externalAccessImapAlterPort !== '' || this.externalAccessPop3Signature !== '' ||
              this.externalAccessPop3Port !== 110 || this.externalAccessPop3AlterPort !== '' ||
              this.externalAccessSmtpSignature !== '' || this.externalAccessSmtpPort !== 25 ||
              this.externalAccessSmtpAlterPort !== ''
        }
        return this.signatureName !== '' || this.domains !== '' || this.imapSignature !== '' || this.imapPort !== 143 ||
            this.imapSsl !== false || this.smtpSignature !== '' || this.smtpPort !== 25 || this.smtpSsl !== false ||
            this.smtpAuthentication !== this.smtpAuthTypeEnum.UseUserCredentials || this.smtpLogin !== '' ||
            this.smtpPassword !== '' || this.enableSieve !== false || this.sievePort !== 4190 ||
            this.useThreading !== true || this.useFullEmail !== true || this.setExternalAccessSignatures !== false ||
            isExternalAccessChanged || this.oauthConnector !== ''
      } else {
        const signature = this.getSignature(this.currentSignatureId)
        if (signature) {
          let isExternalAccessChanged = signature.setExternalAccessSignatures !== this.setExternalAccessSignatures
          if (!isExternalAccessChanged && this.setExternalAccessSignatures) {
            isExternalAccessChanged = signature.externalAccessImapSignature !== this.externalAccessImapSignature ||
                signature.externalAccessImapPort !== this.externalAccessImapPort ||
                signature.externalAccessImapAlterPort !== this.externalAccessImapAlterPort ||
                signature.externalAccessPop3Signature !== this.externalAccessPop3Signature ||
                signature.externalAccessPop3Port !== this.externalAccessPop3Port ||
                signature.externalAccessPop3AlterPort !== this.externalAccessPop3AlterPort ||
                signature.externalAccessSmtpSignature !== this.externalAccessSmtpSignature ||
                signature.externalAccessSmtpPort !== this.externalAccessSmtpPort ||
                signature.externalAccessSmtpAlterPort !== this.externalAccessSmtpAlterPort
          }
          return signature.name !== this.signatureName || signature.incomingSignature !== this.imapSignature ||
              signature.incomingPort !== this.imapPort || signature.incomingUseSsl !== this.imapSsl ||
              signature.outgoingSignature !== this.smtpSignature || signature.outgoingPort !== this.smtpPort ||
              signature.outgoingUseSsl !== this.smtpSsl || signature.domains !== this.domains ||
              signature.smtpAuthType !== this.smtpAuthentication || signature.smtpLogin !== this.smtpLogin ||
              signature.smtpPassword !== this.smtpPassword || signature.enableSieve !== this.enableSieve ||
              signature.sievePort !== this.sievePort || signature.enableThreading !== this.useThreading ||
              signature.useFullEmailAddressAsLogin !== this.useFullEmail ||
              isExternalAccessChanged || signature.oauthType !== this.oauthConnector
        } else {
          return false
        }
      }
    },

    /**
     * Method is used in doBeforeRouteLeave mixin,
     * do not use async methods - just simple and plain reverting of values
     * !! hasChanges method must return true after executing revertChanges method
     */
    revertChanges () {
      this.populateSignature()
    },

    getSignature (id) {
      return this.signatures.find(signature => {
        return signature.id === id
      })
    },

    updateSignature (parameters) {
      const signature = this.getSignature(parameters.SignatureId)
      if (signature) {
        signature.update(parameters)
      }
    },

    getSaveParameters () {
      const parameters = {
        Name: this.signatureName,
        IncomingSignature: this.imapSignature,
        IncomingPort: this.imapPort,
        IncomingUseSsl: this.imapSsl,
        OutgoingSignature: this.smtpSignature,
        OutgoingPort: this.smtpPort,
        OutgoingUseSsl: this.smtpSsl,
        Domains: this.domains,
        SmtpAuthType: this.smtpAuthentication,
        SmtpLogin: this.smtpLogin,
        SmtpPassword: this.smtpPassword,
        EnableSieve: this.enableSieve,
        SievePort: this.sievePort,
        EnableThreading: this.useThreading,
        UseFullEmailAddressAsLogin: this.useFullEmail,
        SetExternalAccessSignatures: this.setExternalAccessSignatures,
        ExternalAccessImapSignature: this.externalAccessImapSignature,
        ExternalAccessImapPort: typesUtils.pInt(this.externalAccessImapPort),
        ExternalAccessImapAlterPort: typesUtils.pInt(this.externalAccessImapAlterPort),
        ExternalAccessPop3Signature: this.externalAccessPop3Signature,
        ExternalAccessPop3Port: typesUtils.pInt(this.externalAccessPop3Port),
        ExternalAccessPop3AlterPort: typesUtils.pInt(this.externalAccessPop3AlterPort),
        ExternalAccessSmtpSignature: this.externalAccessSmtpSignature,
        ExternalAccessSmtpPort: typesUtils.pInt(this.externalAccessSmtpPort),
        ExternalAccessSmtpAlterPort: typesUtils.pInt(this.externalAccessSmtpAlterPort)
      }

      const isOAuthEnable = this.oauthConnector !== ''
      const selectedConnector = isOAuthEnable
        ? this.oauthConnectorsData.find(data => {
          return data.type === this.oauthConnector
        })
        : null
      if (selectedConnector) {
        parameters.OAuthEnable = true
        parameters.OAuthName = selectedConnector.name
        parameters.OAuthType = selectedConnector.type
        parameters.OAuthIconUrl = selectedConnector.iconUrl
      } else {
        parameters.OAuthEnable = false
        parameters.OAuthName = ''
        parameters.OAuthType = ''
        parameters.OAuthIconUrl = ''
      }

      return parameters
    },

    isDataValid () {
      let emptyField = ''
      if (_.isEmpty(_.trim(this.signatureName))) {
        emptyField = 'signatureName'
      } else if (_.isEmpty(_.trim(this.imapSignature))) {
        emptyField = 'imapSignature'
      } else if (_.isEmpty(_.trim(this.imapPort))) {
        emptyField = 'imapPort'
      } else if (_.isEmpty(_.trim(this.smtpSignature))) {
        emptyField = 'smtpSignature'
      } else if (_.isEmpty(_.trim(this.smtpPort))) {
        emptyField = 'smtpPort'
      }
      if (!_.isEmpty(emptyField)) {
        if (_.isFunction(this.$refs[emptyField]?.$el?.focus)) {
          this.$refs[emptyField].$el.focus()
        }
        notification.showError(this.$t('COREWEBCLIENT.ERROR_REQUIRED_FIELDS_EMPTY'))
        return false
      }
      return true
    },

    save () {
      if (!this.saving && this.isDataValid()) {
        this.saving = true
        const parameters = _.extend(this.getSaveParameters(), {
          SignatureId: this.currentSignatureId,
          TenantId: this.currentSignatureTenantId,
        })
        webApi.sendRequest({
          moduleName: 'Mail',
          methodName: 'UpdateSignature',
          parameters,
        }).then(result => {
          this.saving = false
          if (result === true) {
            this.updateSignature(parameters)
            this.populateSignature()
            this.populate()
            notification.showReport(this.$t('COREWEBCLIENT.REPORT_SETTINGS_UPDATE_SUCCESS'))
          } else {
            notification.showError(this.$t('COREWEBCLIENT.ERROR_SAVING_SETTINGS_FAILED'))
          }
        }, error => {
          this.saving = false
          notification.showError(errors.getTextFromResponse(error, this.$t('COREWEBCLIENT.ERROR_SAVING_SETTINGS_FAILED')))
        })
      }
    },

    addNewSignature () {
      this.$router.push('/system/mail-signatures/create')
    },

    cancelCreate () {
      this.$router.push('/system/mail-signatures')
    },

    create () {
      if (!this.creating && this.isDataValid()) {
        this.creating = true
        const parameters = _.extend(this.getSaveParameters(), {
          TenantId: this.selectedTenantId,
        })
        webApi.sendRequest({
          moduleName: 'Mail',
          methodName: 'CreateSignature',
          parameters,
        }).then(result => {
          this.creating = false
          if (_.isSafeInteger(result)) {
            this.populateSignature()
            this.populate()
            this.$router.push('/system/mail-signatures/id/' + result)
            notification.showReport(this.$t('COREWEBCLIENT.REPORT_SETTINGS_UPDATE_SUCCESS'))
          } else {
            notification.showError(this.$t('COREWEBCLIENT.ERROR_SAVING_SETTINGS_FAILED'))
          }
        }, error => {
          this.creating = false
          notification.showError(errors.getTextFromResponse(error, this.$t('COREWEBCLIENT.ERROR_SAVING_SETTINGS_FAILED')))
        })
      }
    },

    askDeleteSignature(name, id, tenantId) {
      if (_.isFunction(this?.$refs?.confirmDialog?.openDialog)) {
        this.$refs.confirmDialog.openDialog({
          title: name,
          message: this.$t('MAILDOMAINSGLOBALSIGNATURE.CONFIRM_REMOVE_SERVER'),
          okHandler: this.deleteSignature.bind(this, id, tenantId)
        })
      }
    },

    deleteSignature (id, tenantId) {
      this.loadingSignatures = true
      webApi.sendRequest({
        moduleName: 'Mail',
        methodName: 'DeleteSignature',
        parameters: {
          SignatureId: id,
          TenantId: tenantId,
          DeletionConfirmedByAdmin: true
        },
      }).then(result => {
        this.loadingSignatures = false
        if (result === true) {
          if (this.signatures.length > 1 || this.selectedPage === 1) {
            this.populate()
          } else {
            this.selectedPage -= 1
            this.route()
          }
        } else {
          notification.showError(this.$t('MAILDOMAINSGLOBALSIGNATURE.ERROR_DELETE_MAIL_SERVER'))
        }
      }, error => {
        this.loadingSignatures = false
        notification.showError(errors.getTextFromResponse(error, this.$t('MAILDOMAINSGLOBALSIGNATURE.ERROR_DELETE_MAIL_SERVER')))
      })
    },
  },
}
</script>

<style scoped>

</style>
