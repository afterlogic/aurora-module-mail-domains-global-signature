export default {
  moduleName: 'MailDomainsGlobalSignature',

  requiredModules: ['Mail', 'MailDomains'],

  getAdminSystemTabs () {
    return [
      {
        tabName: 'mail-global-signatures',
        paths: [
          'mail-global-signatures',
          'mail-global-signatures/id/:id',
          'mail-global-signatures/create',
          'mail-global-signatures/search/:search',
          'mail-global-signatures/search/:search/id/:id',
          'mail-global-signatures/page/:page',
          'mail-global-signatures/page/:page/id/:id',
          'mail-global-signatures/search/:search/page/:page',
          'mail-global-signatures/search/:search/page/:page/id/:id',
        ],
        title: 'MAILDOMAINSGLOBALSIGNATURE.LABEL_ALL_GLOBAL_SIGNATURES_SETTINGS_TAB',
        component() {
          return import('./components/MailGlobalSignaturesAdminSettings')
        },
      },
    ]
  },

  getAdminDomainTabs () {
    return [
      {
        tabName: 'mail-global-signature',
        paths: [
          'id/:id/mail-global-signature',
          'search/:search/id/:id/mail-global-signature',
          'page/:page/id/:id/mail-global-signature',
          'search/:search/page/:page/id/:id/mail-global-signature',
        ],
        title: 'MAILDOMAINSGLOBALSIGNATURE.LABEL_DOMAIN_GLOBAL_SIGNATURE_SETTINGS_TAB',
        component() {
          return import('./components/MailGlobalSignatureAdminSettingsPerDomain')
        }
      }
    ]
  },

  getAdminUserTabs () {
    return [
      {
        tabName: 'mail-global-signature',
        paths: [
          'id/:id/mail-global-signature',
          'search/:search/id/:id/mail-global-signature',
          'page/:page/id/:id/mail-global-signature',
          'search/:search/page/:page/id/:id/mail-global-signature',
        ],
        title: 'MAILDOMAINSGLOBALSIGNATURE.LABEL_USER_GLOBAL_SIGNATURE_SETTINGS_TAB',
        component() {
          return import('./components/MailGlobalSignatureAdminSettingsPerUser')
        }
      }
    ]
  },
}
