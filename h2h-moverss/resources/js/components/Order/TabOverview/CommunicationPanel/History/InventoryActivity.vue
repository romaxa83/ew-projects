<template>
    <li class="fs-sm text-center">
        {{ datetime }}. {{ actor }} {{ action }} Inventory
        <strong>{{ item }}</strong>
    </li>
</template>

<script>
export default {
    name: 'InventoryActivity',
    props: ['record', 'datetime', 'interface', 'index'],
    computed: {
        actor() {
            if (this.record.item.is_client_action) {
                const {name = '', lname = ''} = this.record.item.client || {};
                const clientName = [name, lname].join(' ').trim();
                return clientName || 'Customer';
            } else {
                return this.record.item.user?.name || 'Employee';
            }
        },
        action() {
            switch (this.record.item.action) {
                case 'create':
                    return 'created';
                case 'update':
                    return 'updated';
                case 'delete':
                    return 'deleted';
                default:
                    return 'interacted with';
            }
        },
        item() {
            const {title, qty} = this.record.item?.miscs || {};
            const label = [title, qty].join('; ').trim().replace(/(^;)|(;$)/g, '');
            return label ? `(${label})` : '';
        },
    },
};
</script>
