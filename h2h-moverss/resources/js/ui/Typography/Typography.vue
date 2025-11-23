<template>
    <component :is="htmlElement"><slot /></component>
</template>

<script>
export default {
    name: 'UiTypography',
    props: {
        /**
         * The variant of the typography.
         * values: 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'body1', 'body2', 'caption'
         */
        variant: {
            type: String,
            default: 'body1',
        },
        element: {
            type: String,
            required: false,
        },
    },
    computed: {
        htmlElement() {
            if (this.element) return this.element;
            switch (this.variant) {
                case 'body1':
                case 'body2':
                    return 'p';
                default:
                    return this.variant;
            }
        }
    }
};
</script>
