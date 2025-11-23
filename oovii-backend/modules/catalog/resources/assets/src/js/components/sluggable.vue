<template>
    <div class="position-relative">
        <b-input-group>
            <b-form-input :value="value"
                          :name="name"
                          @input="setValue($event)"
                          autocomplete="off"
                          v-validate="{required: true, max: 255, alpha_dash: true}"
                          :data-vv-as="translations.slug"
                          :class="{ 'is-invalid': errors.has(name) }"> </b-form-input>
            <b-input-group-append>
                <b-btn variant="outline-secondary" v-b-tooltip.hover :title="title"
                       @click="generateSlug"><i class="fa fa-cogs"></i></b-btn>
            </b-input-group-append>
        </b-input-group>
        <b-form-invalid-feedback v-if="errors.has(name)">
            {{ errors.first(name) }}
        </b-form-invalid-feedback>
    </div>
</template>
<script>
    export default {
        props: {
        	  id: Number || String,
            source: String,
            value: String,
            name: String,
            validator: Object,
            separator: {
                type: String,
                default: '-'
            },
            language: {
                type: String,
                default: function () {
                    return document.documentElement.lang || 'en'
                }
            },
            title: {
                type: String,
                default: translations.generateSlug
            },
            translations: Object
        },
        created () {
          this.$validator = this.validator
        },
        methods: {
            generateSlug() {
                let $source = $(this.source);
                if ($source.length === 0) {
                    console.warn('Cant`t find sluggable source [' + this.source + ']');
                    return;
                }
                let _this = this;

                let requestData = {
                    slug: $source.val(),
                    separator: this.separator,
                    language: this.language
                };

                this.$http.post(route('admin.ajax.generate-slug'), requestData)
                    .then((response) => {
                    if (response.data.success) {
                        _this.setValue(response.data.slug);
                    }
                });
            },
            setValue (value) {
                this.$emit('input', value);
            },
        }
    }
</script>
