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
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-btn dense flat no-caps color="negative" class="no-hover" :label="$t('COREWEBCLIENT.ACTION_DELETE')"
                       @click.native.stop="askDeleteSignature(signature)"/>
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
            <q-card-section class="text-caption" v-t="'MAILDOMAINSGLOBALSIGNATURE.INFO_NO_SIGNATURES'" />
          </q-card>
          <q-card flat bordered class="card-edit-settings"
                  v-if="signatures.length === 0 && !loadingSignatures && search !== ''">
            <q-card-section class="text-caption" v-t="'MAILDOMAINSGLOBALSIGNATURE.INFO_NO_SIGNATURES_FOUND'" />
          </q-card>
        </div>

        <q-card flat bordered class="card-edit-settings" v-if="showSignatureFields || createMode">
          <q-card-section>
            <div class="row">
              <div class="col-2 q-my-sm q-pl-sm required-field" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_SIGNATURE_NAME'"></div>
              <div class="col-5">
                <q-input outlined dense bg-color="white" v-model="signatureName" ref="signatureName"></q-input>
              </div>
            </div>
            <div class="row q-mt-sm q-mb-lg">
              <div class="col-2 q-my-sm q-pl-sm required-field" v-t="'MAILDOMAINSGLOBALSIGNATURE.LABEL_SIGNATURE'"></div>
              <div class="col-9">
                <q-editor class="full-height" outlined
                          ref="editor"
                          v-model="signatureHtml"
                          height="calc(100% - 32px)"
                          :toolbar="editorToolbar"
                          :definitions="editorToolbarDefinitions"
                          :fonts="{
                            arial: 'Arial',
                            arial_black: 'Arial Black',
                            courier_new: 'Courier New',
                            tahoma: 'Tahoma',
                            times_new_roman: 'Times New Roman',
                            verdana: 'Verdana'
                          }"
                >
                  <template v-slot:image>
                    <q-btn-dropdown
                        flat
                        dense
                        size="sm"
                        class="arrowless"
                        icon="image"
                        ref="insertImageDropdown"
                        @hide="imageUrl=''"
                    >
                      <template v-slot:label>
                        <q-tooltip>{{$t('MAILWEBCLIENT.ACTION_INSERT_IMAGE')}}</q-tooltip>
                      </template>

                      <q-card class="">
                        <q-item-label header v-t="'MAILWEBCLIENT.LABEL_SELECT_IMAGE'"></q-item-label>
                        <q-item>
                          <q-btn outline class="full-width" color="primary" @click="insertImage" :label="$t('MAILWEBCLIENT.ACTION_CHOOSE_FILE')" />
                        </q-item>

                        <q-item-label header v-t="'MAILWEBCLIENT.LABEL_ENTER_IMAGE_URL'"></q-item-label>
                        <q-item>
                          <q-input outlined dense type="text" class="full-width" v-model="imageUrl" />
                        </q-item>

                        <q-card-actions align="right">
                          <q-btn flat color="primary" :label="$t('MAILWEBCLIENT.ACTION_INSERT')" @click="insertImageByUrl" />
                          <q-btn flat color="grey-6" :label="$t('COREWEBCLIENT.ACTION_CANCEL')" @click="cancelInsertImage" />
                        </q-card-actions>
                      </q-card>
                      <div>
                      </div>
                    </q-btn-dropdown>
                  </template>
                </q-editor>
                <q-uploader style="display: none;"
                            ref="imageUploader"
                            hide-upload-btn
                            :accept="acceptedImageTypes"
                            @added="onImageFileAdded"
                >
                  <!-- <template v-slot:header="scope">
                    <q-uploader-add-trigger />
                  </template>
                  <template v-slot:list="scope" >
                  </template> -->
                </q-uploader>
              </div>
            </div>
            <div class="row">
              <div class="col-10">
                <q-item-label caption v-t="'MAILDOMAINSGLOBALSIGNATURE.HINT_SIGNATURE_PLACEHOLDERS'"/>
              </div>
            </div>
            <div class="row">
              <div class="col-10">
                <ul>
                  <li v-for="placeholder in placeholders" :key="placeholder">
                    <q-item-label caption>
                    {{placeholder}}
                    </q-item-label>
                  </li>
                </ul>
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

import notification from 'src/utils/notification'
import typesUtils from 'src/utils/types'

import globalSignaturesWebApi from '../global-signatures-web-api'

import ConfirmDialog from 'src/components/ConfirmDialog'

export default {
  name: 'MailGlobalSignaturesAdminSettings',

  components: {
    ConfirmDialog,
  },

  data() {
    return {
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

      createMode: false,
      showSignatureFields: false,

      signatureName: '',
      signatureHtml: '',
      placeholders: ['{{Name}}', '{{Position}}', '{{Phone}}', '{{Email}}'],

      acceptedImageTypes: 'image/*',
      imageUrl: '',

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

    editorToolbarDefinitions () {
      return {
        undo: { tip: this.$t('MAILWEBCLIENT.ACTION_UNDO') },
        redo: { tip: this.$t('MAILWEBCLIENT.ACTION_REDO') },
        bold: { tip: this.$t('MAILWEBCLIENT.ACTION_SET_BOLD') },
        italic: { tip: this.$t('MAILWEBCLIENT.ACTION_SET_ITALIC') },
        underline: { tip: this.$t('MAILWEBCLIENT.ACTION_SET_UNDERLINE') },
        strike: { tip: this.$t('MAILWEBCLIENT.ACTION_SET_STRIKETHROUGH') },
        'size-2': { tip: this.$t('MAILWEBCLIENT.ACTION_CHOOSE_SMALL_TEXTSIZE') },
        'size-3': { tip: this.$t('MAILWEBCLIENT.ACTION_CHOOSE_NORMAL_TEXTSIZE') },
        'size-5': { tip: this.$t('MAILWEBCLIENT.ACTION_CHOOSE_LARGE_TEXTSIZE') },
        'size-7': { tip: this.$t('MAILWEBCLIENT.ACTION_CHOOSE_HUGE_TEXTSIZE') },
        colors: { tip: this.$t('MAILWEBCLIENT.ACTION_SET_COLOR') },
        unordered: { tip: this.$t('MAILWEBCLIENT.ACTION_SET_BULLETS') },
        ordered: { tip: this.$t('MAILWEBCLIENT.ACTION_SET_NUMBERING') },
        link: { tip: this.$t('MAILWEBCLIENT.ACTION_INSERT_LINK') },
        removeFormat: { tip: this.$t('MAILWEBCLIENT.ACTION_REMOVE_FORMAT') },
      }
    },

    editorToolbar () {
      return [
        ['undo', 'redo'],
        ['bold', 'italic', 'underline', 'strike'],
        [{
          list: 'no-icons',
          options: [
            'default_font',
            'arial',
            'arial_black',
            'courier_new',
            'tahoma',
            'times_new_roman',
            'verdana'
          ],
        }, {
          list: 'no-icons',
          options: [
            'size-2',
            'size-3',
            'size-5',
            'size-7'
          ],
        }, 'colors'],
        ['unordered', 'ordered'],
        ['link', 'image', 'removeFormat']
      ]
    },
  },

  watch: {
    $route (to, from) {
      if (this.$route.path === '/system/mail-global-signatures/create') {
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
  },

  methods: {
    route (signatureId = 0) {
      const searchRoute = this.enteredSearch !== '' ? ('/search/' + this.enteredSearch) : ''
      const selectedPage = (this.search !== this.enteredSearch) ? 1 : this.selectedPage
      const pageRoute = selectedPage > 1 ? ('/page/' + selectedPage) : ''
      const idRoute = signatureId > 0 ? ('/id/' + signatureId) : ''
      const path = '/system/mail-global-signatures' + searchRoute + pageRoute + idRoute
      if (path !== this.$route.path) {
        this.$router.push(path)
      }
    },

    populate () {
      this.loadingSignatures = true
      globalSignaturesWebApi.getSignatures(this.search, this.page, this.limit).then(({ signatures, totalCount, page, search }) => {
        if (page === this.page && search === this.search) {
          this.signatures = signatures
          this.totalCount = totalCount
          if (this.search === '') {
            this.showSearch = totalCount > this.limit
          }
          this.loadingSignatures = false
          if (this.currentSignatureId !== 0) {
            this.populateSignature()
          }
        }
      })
    },

    populateSignature () {
      if (this.createMode) {
        this.currentSignatureId = 0
        this.signatureName = ''
        this.signatureHtml = ''
      } else {
        const signature = this.getSignature(this.currentSignatureId)
        this.showSignatureFields = !!signature
        if (this.showSignatureFields) {
          this.signatureName = signature.name
          this.signatureHtml = signature.html
        }
      }
    },

    /**
     * Method is used in doBeforeRouteLeave mixin
     */
    hasChanges () {
      if (this.createMode) {
        return this.signatureName !== '' || this.signatureHtml !== ''
      } else {
        const signature = this.getSignature(this.currentSignatureId)
        if (signature) {
          return signature.name !== this.signatureName || signature.html !== this.signatureHtml
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

    isDataValid () {
      let emptyField = ''
      if (_.isEmpty(_.trim(this.signatureName))) {
        emptyField = 'signatureName'
      } else if (_.isEmpty(_.trim(this.signatureHtml))) {
        emptyField = 'signatureHtml'
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
        globalSignaturesWebApi.updateGlobalSignature(this.currentSignatureId, this.signatureName, this.signatureHtml).then((isUpdated) => {
          this.saving = false
          if (isUpdated) {
            const signature = this.getSignature(this.currentSignatureId)
            if (signature) {
              signature.update(this.signatureName, this.signatureHtml)
            }
            this.populateSignature()
            this.populate()
          }
        })
      }
    },

    addNewSignature () {
      this.$router.push('/system/mail-global-signatures/create')
    },

    cancelCreate () {
      this.$router.push('/system/mail-global-signatures')
    },

    create () {
      if (!this.creating && this.isDataValid()) {
        this.creating = true
        globalSignaturesWebApi.addGlobalSignature(this.signatureName, this.signatureHtml).then(({ newSignatureId }) => {
          this.creating = false
          if (newSignatureId > 0) {
            this.populateSignature()
            this.populate()
            this.$router.push('/system/mail-global-signatures/id/' + newSignatureId)
          }
        })
      }
    },

    askDeleteSignature(signature) {
      if (_.isFunction(this?.$refs?.confirmDialog?.openDialog)) {
        this.$refs.confirmDialog.openDialog({
          title: signature.name,
          message: this.$t('MAILDOMAINSGLOBALSIGNATURE.CONFIRM_REMOVE_SIGNATURE'),
          okHandler: this.deleteSignature.bind(this, signature.id)
        })
      }
    },

    deleteSignature (id) {
      this.loadingSignatures = true
      globalSignaturesWebApi.deleteGlobalSignature(id).then(({ isDeleted }) => {
        this.loadingSignatures = false
        if (isDeleted) {
          if (this.signatures.length > 1 || this.selectedPage === 1) {
            this.populate()
          } else {
            this.selectedPage -= 1
            this.route()
          }
        }
      })
    },

    insertImage () {
      this.$refs.editor.focus()
      this.$refs.imageUploader.pickFiles()
    },

    insertImageByUrl () {
      this.$refs.editor.focus()
      document.execCommand('insertHTML', true, '<img src="' + this.imageUrl + '" />')
    },

    cancelInsertImage () {
      this.$refs.insertImageDropdown.hide()
    },

    onImageFileAdded (files) {
      if (typesUtils.isNonEmptyArray(files)) {
        files.forEach(file => {
          const reader = new FileReader()
          reader.onloadend = () => {
            this.$refs.editor.focus()
            document.execCommand('insertHTML', true, `<img src="${reader.result}" />`)
          }
          reader.readAsDataURL(file)
        })
      }
      return false
    },
  },
}
</script>

<style scoped>
  li {
    color: rgba(0, 0, 0, 0.54);
  }
</style>
