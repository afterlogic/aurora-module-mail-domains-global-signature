import MailGlobalSignaturesAdminSettings from './components/MailGlobalSignaturesAdminSettings'
import MailGlobalSignatureAdminSettingsPerDomain from './components/MailGlobalSignatureAdminSettingsPerDomain'
import MailGlobalSignatureAdminSettingsPerUser from './components/MailGlobalSignatureAdminSettingsPerUser'

import store from 'src/store'

export default {
  moduleName: 'MailDomainsGlobalSignature',

  requiredModules: ['Mail', 'MailDomains'],

  getAdminSystemTabs () {
    return [
      {
        tabName: 'mail-global-signatures',
        tabTitle: 'MAILDOMAINSGLOBALSIGNATURE.LABEL_ALL_GLOBAL_SIGNATURES_SETTINGS_TAB',
        tabRouteChildren: [
          { path: 'mail-global-signatures', component: MailGlobalSignaturesAdminSettings },
          { path: 'mail-global-signatures/id/:id', component: MailGlobalSignaturesAdminSettings },
          { path: 'mail-global-signatures/create', component: MailGlobalSignaturesAdminSettings },
          { path: 'mail-global-signatures/search/:search', component: MailGlobalSignaturesAdminSettings },
          { path: 'mail-global-signatures/search/:search/id/:id', component: MailGlobalSignaturesAdminSettings },
          { path: 'mail-global-signatures/page/:page', component: MailGlobalSignaturesAdminSettings },
          { path: 'mail-global-signatures/page/:page/id/:id', component: MailGlobalSignaturesAdminSettings },
          { path: 'mail-global-signatures/search/:search/page/:page', component: MailGlobalSignaturesAdminSettings },
          { path: 'mail-global-signatures/search/:search/page/:page/id/:id', component: MailGlobalSignaturesAdminSettings },
        ],
      },
    ]
  },

  getAdminDomainTabs () {
    return [
      {
        tabName: 'mail-global-signature',
        tabTitle: 'MAILDOMAINSGLOBALSIGNATURE.LABEL_DOMAIN_GLOBAL_SIGNATURE_SETTINGS_TAB',
        tabRouteChildren: [
          { path: 'id/:id/mail-global-signature', component: MailGlobalSignatureAdminSettingsPerDomain },
          { path: 'search/:search/id/:id/mail-global-signature', component: MailGlobalSignatureAdminSettingsPerDomain },
          { path: 'page/:page/id/:id/mail-global-signature', component: MailGlobalSignatureAdminSettingsPerDomain },
          { path: 'search/:search/page/:page/id/:id/mail-global-signature', component: MailGlobalSignatureAdminSettingsPerDomain },
        ],
      }
    ]
  },

  getAdminUserTabs () {
    const isUserSuperAdmin = store.getters['user/isUserSuperAdmin']
    if (isUserSuperAdmin) {
      return [
        {
          tabName: 'mail-global-signature',
          tabTitle: 'MAILDOMAINSGLOBALSIGNATURE.LABEL_USER_GLOBAL_SIGNATURE_SETTINGS_TAB',
          tabRouteChildren: [
            { path: 'id/:id/mail-global-signature', component: MailGlobalSignatureAdminSettingsPerUser },
            { path: 'search/:search/id/:id/mail-global-signature', component: MailGlobalSignatureAdminSettingsPerUser },
            { path: 'page/:page/id/:id/mail-global-signature', component: MailGlobalSignatureAdminSettingsPerUser },
            { path: 'search/:search/page/:page/id/:id/mail-global-signature', component: MailGlobalSignatureAdminSettingsPerUser },
          ],
        },
      ]
    }
    return []
  },
}
