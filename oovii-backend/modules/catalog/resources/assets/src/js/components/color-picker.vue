<template>
    <b-input-group class="color-picker" ref="colorpicker">
        <b-form-input :value="value" @input="setValue" @focus="showPicker()"></b-form-input>
        <b-input-group-append class="color-picker-container">
            <b-btn variant="outline-secondary" @click="togglePicker()">
                <span class="current-color" :style="'background-color: ' + value"></span>
            </b-btn>
            <chrome-picker :value="colors" @input="updateFromPicker" v-if="displayPicker" />
        </b-input-group-append>
    </b-input-group>
</template>
<script>
import ChromePicker from 'vue-color/src/components/Chrome';

export default {
        props: {
            value: String,
        },
        components: {
            'chrome-picker': ChromePicker,
        },
        data () {
            return {
                colors: {
                    hex: '#000000',
                },
                displayPicker: false,
            }
        },
        mounted() {
          this.updateColors(this.value);
        },
        methods: {
            updateColors(color) {
                if(color.slice(0, 1) == '#') {
                    this.colors = {
                        hex: color
                    };
                }
                else if(color.slice(0, 4) == 'rgba') {
                    var rgba = color.replace(/^rgba?\(|\s+|\)$/g,'').split(','),
                        hex = '#' + ((1 << 24) + (parseInt(rgba[0]) << 16) + (parseInt(rgba[1]) << 8) + parseInt(rgba[2])).toString(16).slice(1);
                    this.colors = {
                        hex: hex,
                        a: rgba[3],
                    }
                }
            },
            showPicker() {
                document.addEventListener('click', this.documentClick);
                this.displayPicker = true;
            },
            hidePicker() {
                document.removeEventListener('click', this.documentClick);
                this.displayPicker = false;
            },
            togglePicker() {
                this.displayPicker ? this.hidePicker() : this.showPicker();
            },
            updateFromPicker(color) {
                let value = '';
                this.colors = color;
                if(color.rgba.a == 1) {
                    value = color.hex;
                }
                else {
                    value = 'rgba(' + color.rgba.r + ', ' + color.rgba.g + ', ' + color.rgba.b + ', ' + color.rgba.a + ')';
                }
				        this.$emit('input', value);
            },
            setValue(value) {
              this.$emit('input', value);
              this.updateColors(value);
            },
            documentClick(e) {
                var el = this.$refs.colorpicker,
                    target = e.target;
                if(el !== target && !el.contains(target)) {
                    this.hidePicker()
                }
            }
        }
    }
</script>
<style>
    .vc-chrome {
        position: absolute;
        top: 35px;
        right: 0;
        z-index: 9;
    }
    .current-color {
        display: block;
        width: 15px;
        height: 15px;
        background-color: #000;
        cursor: pointer;
    }
</style>
