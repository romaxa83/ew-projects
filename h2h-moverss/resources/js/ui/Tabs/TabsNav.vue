<template>
    <ul class="nav nav-tabs" role="tablist">
        <li
            v-for="tab in tabs"
            :key="tab.value"
            class="nav-item"
        >
            <button
                class="nav-link"
                :class="tab.value === active && 'active'"
                role="tab"
                type="button"
                :value="tab.value"
                @click="() => $emit('tab-click', tab.value)"
            >
                <i v-if="tab.hasWarn" class="mr-1 fas fa-exclamation-triangle"></i>
                {{ tab.label }}
            </button>
        </li>
    </ul>
</template>

<script>
export default {
    name: 'UiTabsNav',
    props: {
        tabs: {
            type: Array,
            required: true,
        },
        active: {
            type: String,
            required: true,
        },
    }
}
</script>
