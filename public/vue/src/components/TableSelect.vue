<template>
    <div>
        <div v-if="disabled">
            <span class="span-name">{{selectLabel}}</span>
        </div>
        <div v-else>
            <div v-if="filterable">
                <span v-if="!showSelect" @click="showSelect = !showSelect" class="span-name">{{selectLabel}}</span>
                <Select v-else v-model="selectValue" filterable label-in-value remote :remote-method="remoteMethod1"
                        :loading="loading1" @on-change="checkLabel">
                    <Option v-for="(option, index) in options1" :value="option.value" :key="index">{{option.label}}</Option>
                </Select>
            </div>
            <div v-else>
                <span v-if="!showSelect" @click="showSelect = !showSelect" class="span-name">{{selectLabel}}</span>
                <Select v-else v-model="selectValue" label-in-value @on-change="checkLabel">
                    <Option v-for="item in list" :value="item[listId]" :key="item[listId]">{{ item[listLable] }}</Option>
                </Select>
            </div>
        </div>

    </div>

</template>

<script>
    export default {
        props: ['value', 'label', 'list', 'listId', 'listLable', 'filterable', 'disabled'],
        data() {
            return {
                showSelect: false,
                selectValue: '',
                selectLabel: '',
                loading1: false,
                options1: [],
            }
        },
        methods: {
            checkLabel(data) {
                this.$emit('on-change', data)
            },
            remoteMethod1(query) {
                if (query !== '') {
                    this.loading1 = true;
                    setTimeout(() => {
                        this.loading1 = false;
                        var that = this
                        const list = this.list.map(item => {
                            return {
                                value: item[that.listId],
                                label: item[that.listLable]
                            };
                        });
                        this.options1 = list.filter(item => item.label.toLowerCase().indexOf(query.toLowerCase()) > -1);
                    }, 200);
                } else {
                    this.options1 = [];
                }
            }
        },
        created() {
            var fop = []
            fop.value = this.value
            fop.label = this.label
            this.options1.push(fop)
            this.selectValue = this.value
            this.selectLabel = this.label
            document.addEventListener('click', (e) => {
                if (!this.$el.contains(e.target)) {
                    this.showSelect = false;
                }
            }, false)
        },
        watch: {
            value(value, old) {
                var fop = []
                fop.value = this.value
                fop.label = this.label
                this.options1.push(fop)
                this.selectValue = this.value
                this.selectLabel = this.label
            }
        }
    }
</script>

<style scoped>
    .span-name {
        display: block;
        cursor: pointer;
        width: 100%;
        min-height: 20px;
    }
</style>
