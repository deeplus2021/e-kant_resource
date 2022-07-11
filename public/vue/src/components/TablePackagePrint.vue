<template>
    <div>
        <Row style="margin-left: 20px;" v-if="showButtons">
            <Button type="success" @click="tableAdd" :loading="addLoading"
                    v-if="enableAdd === undefined ? true: enableAdd">
                <Icon type="md-add"/>
                新建
            </Button>
            <Button type="info" @click="tableChange" :disabled="changeButton" :loading="changeLoading">
                <Icon type="ios-paper-outline"/>
                编辑
            </Button>
            <Button type="error" @click="tableDelete" :disabled="deleteButton" :loading="deleteLoading">
                <Icon type="md-close"/>
                删除
            </Button>
            <Button type="primary" @click="tablePrint" :disabled="printButton" :loading="deleteLoading">
                <Icon type="md-print"/>
                打印
            </Button>
        </Row>
        <Row style="margin: 10px 20px;">
            <Table stripe size="small" :columns="tableColumns" :data="tableData" :height="tableHeight"
                   :loading="loading"
                   @on-selection-change="selectChange" @on-row-click="rowClick" highlight-row
                   @on-current-change="currentChange" :span-method="handleSpan" @on-row-dblclick="rowDbclick"></Table>
            <Page style="margin-top: 10px;" :total="total" @on-change="changepage"/>
        </Row>
    </div>
</template>

<script>
    export default {
        props: {
            loading: Boolean,
            tableColumns: Array,
            tableData: Array,
            tableHeight: Number,
            pageTotal: Number,
            pagePage: Number,
            showButtons: {
                type: Boolean,
                default: true
            },
            enableAdd: {
                type: Boolean,
                default: true
            },
            handleSpans: {
                type: Function,
                default: function ({row, column, rowIndex, columnIndex}) {
                    return [1, 1]
                }
            }
        },
        data() {
            return {
                selectOption: [],
                changeLoading: false,
                deleteLoading: false,
                addLoading: false
            }
        },
        methods: {
            selectChange(data) {
                this.selectOption = data

                this.$emit('selectChange', data)
            },
            changepage(page) {
                this.$emit('changepage', page)
            },
            tableAdd() {
                this.addLoading = true
                setTimeout(() => {
                    this.addLoading = false
                }, "3000");
                this.$emit('tableAdd')
            },
            tableChange() {
                this.changeLoading = true
                setTimeout(() => {
                    this.changeLoading = false
                }, "1000");
                this.$emit('tableChange', this.selectOption)
            },
            tableDelete() {
                this.deleteLoading = true
                setTimeout(() => {
                    this.deleteLoading = false
                }, "1000");
                this.$emit('tableDelete', this.selectOption)
            },
            tablePrint() {
                this.printLoading = true
                setTimeout(() => {
                    this.printLoading = false
                }, "1000");
                this.$emit('tablePrint', this.selectOption)
            },
            rowClick(data, index) {

                this.$emit('rowClick', data, index)
            },
            currentChange(data) {
                this.$emit('currentChange', data)
            },
            rowDbclick(data, index) {
                this.$emit('rowDbclick', data, index)
            },
            handleSpan({row, column, rowIndex, columnIndex}) {
                return this.handleSpans({row, column, rowIndex, columnIndex})
            },
        },
        mounted() {

        },
        computed: {
            total() {
                return this.pageTotal ? this.pageTotal : 10
            },
            page() {
                return this.pagePage ? this.pagePage : 1
            },
            changeButton() {
                if (this.selectOption.length == 1) {
                    return false
                } else {
                    return true
                }
            },
            printButton() {
                if (this.selectOption.length == 1) {
                    return false
                } else {
                    return true
                }
            },
            deleteButton() {
                if (this.selectOption.length == 0) {
                    return true
                } else {
                    return false
                }
            }
        }
    }
</script>

<style>
</style>
