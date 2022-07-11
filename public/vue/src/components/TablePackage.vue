<template>
    <div>
        <Row style="margin-left: 20px;" v-if="showButtons">
            <Button type="success" @click="tableAdd" :loading="addLoading"
                    v-if="enableAdd === undefined ? true: enableAdd">
                <Icon type="md-add"/>
                新規
            </Button>
            <Button type="info" @click="tableChange" :disabled="changeButton" :loading="changeLoading"
                    v-if="enableChange === undefined ? true: enableChange">
                <Icon type="ios-paper-outline"/>
                編集
            </Button>
            <Button type="error" @click="tableDelete" :disabled="deleteButton" :loading="deleteLoading"
                    v-if="enableDelete === undefined ? true: enableDelete">
                <Icon type="md-close"/>
                削除
            </Button>
        </Row>
        <Row style="margin: 10px 20px;">
            <Table :border="border" stripe size="small" :columns="tableColumns" :data="tableData" :height="tableHeight"
                   :loading="loading"
                   @on-selection-change="selectChange" @on-row-click="rowClick" highlight-row @on-cell-click="cellClick"
                   @on-current-change="currentChange" :span-method="handleSpan" @on-row-dblclick="rowDbclick"></Table>
            <Page v-if="enablePage" style="margin-top: 10px;" :current="page" :total="total" :pageSize="pageSize" @on-change="changepage"/>
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
            pageSize: Number,
            border: {
                type: Boolean,
                default: false
            },
            showButtons: {
                type: Boolean,
                default: true
            },
            enableAdd: {
                type: Boolean,
                default: true
            },
            enableChange: {
                type: Boolean,
                default: true
            },
            enableDelete: {
                type: Boolean,
                default: true
            },
            enablePage: {
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
            cellClick(row, column, data, event){
                this.$emit('on-cell-click', row, column, data, event)
            }
        },
        mounted() {

        },
        computed: {
            total() {
                return this.pageTotal ? this.pageTotal : 1
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
