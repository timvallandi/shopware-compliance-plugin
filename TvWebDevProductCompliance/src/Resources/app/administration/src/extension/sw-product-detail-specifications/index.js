import template from './sw-product-detail-specifications.html.twig';

const { Component } = Shopware;

const FIELD_SET_NAME = 'tvwebdev_product_compliance';
const REQUIRED_FIELD = 'tvwebdev_product_compliance_required';
const NOTICE_FIELD = 'tvwebdev_product_compliance_notice';

Component.override('sw-product-detail-specifications', {
    template,

    computed: {
        tvwebdevFilteredCustomFieldSets() {
            return this.customFieldSets.filter((set) => set.name !== FIELD_SET_NAME);
        },

        tvwebdevShowCustomFieldsCard() {
            return this.showProductCard('custom_fields')
                && !this.isLoading
                && this.tvwebdevFilteredCustomFieldSets.length > 0;
        },

        tvwebdevShowComplianceCard() {
            return this.showProductCard('custom_fields') && !this.isLoading;
        },

        tvwebdevComplianceRequired: {
            get() {
                return !!this.tvwebdevProductCustomFields[REQUIRED_FIELD];
            },

            set(value) {
                this.ensureTvWebDevProductCustomFields();
                this.product.customFields[REQUIRED_FIELD] = !!value;

                if (!value) {
                    this.product.customFields[NOTICE_FIELD] = '';
                }
            },
        },

        tvwebdevComplianceNotice: {
            get() {
                return this.tvwebdevProductCustomFields[NOTICE_FIELD] || '';
            },

            set(value) {
                this.ensureTvWebDevProductCustomFields();
                this.product.customFields[NOTICE_FIELD] = value;
            },
        },

        tvwebdevComplianceNoticeDisabled() {
            return !this.tvwebdevComplianceRequired || !this.acl.can('product.editor');
        },

        tvwebdevProductCustomFields() {
            if (!this.product || !this.product.customFields) {
                return {};
            }

            return this.product.customFields;
        },
    },

    methods: {
        ensureTvWebDevProductCustomFields() {
            if (!this.product) {
                return;
            }

            if (!this.product.customFields) {
                this.product.customFields = {};
            }
        },
    },
});
