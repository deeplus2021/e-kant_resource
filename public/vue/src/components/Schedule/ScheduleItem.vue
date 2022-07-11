<template>
    <div class="s-layout">
        <div class="s-content" id="list" @mouseleave="selOut">
            <div class="s-element"
                 v-for="(item, index) in timelines" :key="index"
                 :class="{'selected':item.selected,'m': index > 0, 'bm':(index % 4 === 0)}"
                 @mousedown="selDown($event,item,index)"
                 @mouseup="selUp($event,item,index)"
                 @mousemove="selMove($event,item,index)"
                 :style="{'background-color': item.number==1?'#5cadff':(item.number==2?'#a1ffde':'#fff')}"
            >
                <div class="drag" v-if="item.s_time==s_time || item.e_time==e_time">
                    {{sp_s}}-{{sp_e}}
                    <span style="float: right" @click.stop="deleteSp"><Icon class="icon-close" type="md-close"/></span>
                    <span style="float: right" @click.stop="getStaffAddress"><Icon class="icon-home"
                                                                                   type="ios-home"/></span>
                    <span style="float: right" @click.stop="editTime(0)"><Icon class="icon-edit"
                                                                               type="ios-alarm"/></span>
                </div>
                <div class="drag-r"
                     v-if="!(item.s_time==s_time || item.e_time==e_time) && (item.s_time==ks_time || (item.e_time==ke_time && (ke_time-ks_time) >60))">
                    {{spr_s}}-{{spr_e}}
                    <span style="float: right" @click.stop="deleteSpr"><Icon class="icon-close" type="md-close"/></span>
                    <span style="float: right" @click.stop="editTime(1)"><Icon class="icon-edit"
                                                                               type="ios-alarm"/></span>
                </div>
                <div class="drag-m"
                     v-if="is_mousedown && (index==s_index && timelines[index].s_time != s_time && timelines[index].s_time != ks_time && timelines[index].e_time != e_time && timelines[index].e_time != ke_time)">
                    {{$utils.Datetimes.num2hi((s_index < e_index)?timelines[s_index].s_time:timelines[s_index].e_time)}}
                </div>
                <div class="drag-m"
                     v-if="is_mousedown && (index==e_index && timelines[index].e_time != e_time && timelines[index].e_time != ke_time && timelines[index].s_time != s_time && timelines[index].s_time != ks_time)">
                    {{$utils.Datetimes.num2hi((s_index < e_index)?timelines[e_index].e_time:timelines[e_index].s_time)}}
                </div>
            </div>
        </div>
        <Modal v-model="edit_modal" :title="edit_modal_type===0?'勤務時間':'勤務休憩時間'" @on-ok="setTime">
            <Row :gutter="16">
                <Form :label-width="80" label-position="right">
                    <Col span="12" style="display: inline-flex;text-align: center">
                        <FormItem label="開始時間" inline>
                            <Select v-model="sel_sh" style="width:55px">
                                <Option v-for="(v, idx) in hours_list" :value="v" :key="idx"
                                        :disabled="edit_modal_type===0?((v*60+sel_sm)>=1440 || (v*60+sel_sm+40)>sel_e):((v*60+sel_sm)>sel_e || (spr_show && (v*60+sel_sm)<s_time))">
                                    {{format(v)}}
                                </Option>
                            </Select>
                            <span class="snap"> : </span>
                            <Select v-model="sel_sm" style="width:55px">
                                <Option v-for="(v, idx) in minutes_list" :value="v" :key="idx"
                                        :disabled="(sel_sh*60+v)>=2160 || (edit_modal_type===0?((sel_sh*60+v)>=1440 || (sel_sh*60+v+40)>sel_e):((sel_sh*60+v)>sel_e || (spr_show && (sel_sh*60+v)<s_time)))">
                                    {{format(v)}}
                                </Option>
                            </Select>
                        </FormItem>
                    </Col>
                    <Col span="12" style="display: inline-flex;text-align: center">
                        <FormItem label="終了時間" inline>
                            <Select v-model="sel_eh" style="width:55px">
                                <Option v-for="(v, idx) in hours_list" :value="v" :key="idx"
                                        :disabled="edit_modal_type===0?((v*60+sel_sm-40)<sel_s) :((v*60+sel_sm)<sel_s || (spr_show && (v*60+sel_sm)>e_time))">
                                    {{format(v)}}
                                </Option>
                            </Select>
                            <span class="snap"> : </span>
                            <Select v-model="sel_em" style="width:55px">
                                <Option v-for="(v, idx) in minutes_list" :value="v" :key="idx"
                                        :disabled="(sel_eh*60+v)>2160 || (edit_modal_type===0?((sel_eh*60+v-40)<sel_s):((sel_eh*60+v)<sel_s || (spr_show && (sel_eh*60+v)>e_time)))">
                                    {{format(v)}}
                                </Option>
                            </Select>
                        </FormItem>
                    </Col>
                </Form>
            </Row>
        </Modal>
        <Modal v-model="edit_address" :title="shift.name + '(' + shift.email + ') 出発先住所選択'"  :mask-closable="false" :closable="true" :footer-hide="true"  width="1250" :styles="{top: '100px'}">
            <Card>
                <Row>
                    <Form :label-width="120" label-position="right">
                        <FormItem label="出発先住所選択" style="margin-bottom: 0">
                            <Button type="info" @click="createAddress">
                                <Icon type="ios-add" size="18"/>
                            </Button>
                        </FormItem>
                        <Col span="24" class="text-center">
                            <table class="address-table">
                                <thead>
                                <tr>
                                    <th style="width: 450px;">出発先住所</th>
                                    <th style="width: 100px;">緯度</th>
                                    <th style="width: 100px;">経度</th>
                                    <th style="width: 50px;">地図表示</th>
                                    <th style="width: 200px;">現場名</th>
                                    <th style="width: 100px;">現場までの時間</th>
                                    <th style="width: 100px;">当日確認メール送信時間</th>
                                    <th style="width: 50px;">削除</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(item, index) in staff_addresses" :key="index">
                                    <td>
                                        <Input v-model="item.address" style="width: 400px;"></Input>
                                    </td>
                                    <td>
                                        <InputNumber v-model="item.latitude" style="width: 100px;"></InputNumber>
                                        <br/>
                                    </td>
                                    <td>
                                        <InputNumber v-model="item.longitude" style="width: 100px;"></InputNumber>
                                        <br/>
                                    </td>
                                    <td>
                                        <Button type="success" @click="view_google_map(index)" size="small"
                                                style="width: 50px;">地図
                                        </Button>
                                    </td>
                                    <td>
                                        <Select v-model="item.field_id" style="width: 200px;">
                                            <Option :value="shift.field_id" :key="shift.field_id" selected>
                                                {{shift.field_name}}
                                            </Option>
                                        </Select>
                                    </td>
                                    <td>
                                        <vTimeSelector v-model="item.required_time"></vTimeSelector>
                                    </td>
                                    <td>
                                        <Select v-model="item.email_time" style="width: 65px;">
                                            <Option v-for="(val, index) in [0, 15, 30, 45, 60, 75, 90, 105, 120]"
                                                    :value="val" :key="index">{{val}}
                                            </Option>
                                        </Select>
                                    </td>
                                    <td>
                                        <Button type="error" size="small" @click="deleteAddress(index)">
                                            <Icon type="ios-trash"/>
                                        </Button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </Col>
                    </Form>
                </Row>
                <br/>
                <Row :gutter="16">
                    <Col span="12" style="text-align: right">
                        <Button type="primary" size="large" @click="updateStaffAddress" :loading="address_loading" style="width: 120px;">登 録</Button>
                    </Col>
                    <Col span="12">
                        <Button type="info" size="large"  @click="toCancel">キャンセル</Button>
                    </Col>
                </Row>
            </Card>

            <Modal v-model="google_map_modal" :mask-closable="false" :closable="true" :footer-hide="true" width="800"
                   :styles="{top: '70px'}">
                <vGoogleMap ref="refGoogleMap" @setPosition="setPosition" @closeMap="closeMap"></vGoogleMap>
            </Modal>
        </Modal>
    </div>
</template>

<script>
    import {
        deleteShifts,
    } from '@/api/shift_master'
    import {
        getStaffAddress,
        updateStaffAddress,
    } from '@/api/staff_master'

    export default {
        name: "ScheduleItem",
        props: {
            value: {
                type: Object,
                default: () => {
                }
            },
            index: {
                type: Number,
                default: null
            },
            field_id: {
                type: Number,
                default: null
            },
            field_name: {
                type: String,
                default: ""
            }
        },
        data() {
            return {
                shift: this.value,
                end_hour: 36,
                timelines: [],
                bc: ["#fff", "#99f", "#9ff"],
                listHeight: 0,
                listWidth: 0,
                edit_modal: false,
                edit_modal_type: 0,
                sel_s: 0,
                sel_e: 0,
                hours_list: [],
                minutes_list: [],
                is_mousedown: false,
                s_index: 0,
                e_index: 0,
                input_number: 0,
                sp_show: true,
                spr_show: true,
                edit_address: false,
                staff_addresses: [],
                google_map_modal: false,
                setMapIndex: 0,
                address_loading: false
            }
        },
        watch: {
            value(val) {
                this.$set(this, "shift", val)
                this.sp_show = true
                if(val.ke_time){
                    this.spr_show = true
                }
                if (val.e_time) {
                    this.changeTimeLines()
                }
            }
        },
        methods: {
            format(v) {
                return v < 10 ? "0" + v : "" + v
            },
            changeTimeLines() {
                for (let t = 0; t < 36 * 60; t += 15) {
                    let number = 0
                    if (this.e_time && t >= this.s_time && (t + 15) <= this.e_time) {
                        number = 1
                        if (this.ke_time && t >= this.ks_time && (t + 15) <= this.ke_time) {
                            number = 2
                        }
                    }
                    this.$set(this.timelines[parseInt(t / 15)], 'number', number)
                }
            },
            deleteSp() {
                if (this.shift.id) {
                    this.deleteShift(this.shift.id)
                } else {
                    this.sp_show = false
                    this.$emit("setShift", this.getShift())
                }
            },
            deleteSpr() {
                this.spr_show = false
                this.shift.ks_time = null
                this.shift.ke_time = null
                this.changeTimeLines()
                this.$emit("setShift", this.getShift())
            },
            getShift() {
                return {
                    index: this.index,
                    is_delete: !this.sp_show,
                    s_time: this.s_time,
                    e_time: this.e_time,
                    ks_time: this.spr_show ? this.ks_time : null,
                    ke_time: this.spr_show ? this.ke_time : null,
                }
            },
            deleteShift(shift_id) {
                let params = {
                    shift_ids: [shift_id]
                };
                this.$Modal.confirm({
                    title: '削除',
                    content: '削除しますか？',
                    okText: "はい",
                    cancelText: "いいえ",
                    onOk: () => {
                        deleteShifts(params).then(res => {
                            if (res.data.status == 'success') {
                                this.$Message.success('正常に削除されました');
                                this.sp_show = false
                                this.$emit("setShift", this.getShift())
                            } else {
                                this.$Message.error('削除に失敗しました');
                            }
                        })
                    },
                    onCancel: () => {
                    },
                });
            },
            editTime(type) {
                this.edit_modal_type = type
                if (type === 0) {
                    this.$set(this, "sel_s", this.s_time)
                    this.$set(this, "sel_e", this.e_time)
                } else {
                    this.$set(this, "sel_s", this.ks_time)
                    this.$set(this, "sel_e", this.ke_time)
                }
                this.edit_modal = true
            },
            setTime() {
                if (this.edit_modal_type === 0) {
                    this.$set(this.shift, 's_time', this.sel_s)
                    this.$set(this.shift, 'e_time', this.sel_e)
                } else {
                    this.$set(this.shift, 'ks_time', this.sel_s)
                    this.$set(this.shift, 'ke_time', this.sel_e)
                }

                this.changeTimeLines()
                this.$emit("setShift", this.getShift())
            },

            selDown(event, item, index) {
                if (event.target.localName === "i") {
                    return
                }
                this.is_mousedown = true
                this.$set(item, "selected", true)
                this.s_index = index
            },
            selUp(event, item, index) {
                if (event.target.localName === "i") {
                    return
                }
                if (!this.is_mousedown) return
                this.is_mousedown = false
                this.e_index = index
                let s_index = Math.min(this.s_index, this.e_index)
                let e_index = Math.max(this.s_index, this.e_index)

                for (let i = s_index; i <= e_index; i++) {
                    this.$set(this.timelines[i], "selected", false)
                }

                if (this.timelines[s_index].number > 0 && this.timelines[e_index].number > 0) {
                    if (this.timelines[s_index].s_time == this.shift.s_time) {
                        this.shift.s_time = this.timelines[e_index].s_time
                        if (this.shift.ks_time && this.timelines[e_index].s_time >= this.shift.ks_time) {
                            this.shift.ks_time = this.timelines[e_index].s_time + 15
                            if (this.shift.ks_time >= this.shift.ke_time) {
                                this.deleteSpr()
                            }
                        }
                    } else if (this.timelines[e_index].e_time == this.shift.e_time) {
                        this.shift.e_time = this.timelines[s_index].e_time
                        if (this.shift.ke_time && this.timelines[s_index].e_time <= this.shift.ke_time) {
                            this.shift.ke_time = this.timelines[s_index].e_time - 15
                            if (this.shift.ks_time >= this.shift.ke_time) {
                                this.deleteSpr()
                            }
                        }
                    } else {
                        this.spr_show = true
                        this.$set(this.shift, 'ks_time', this.timelines[s_index].s_time)
                        this.$set(this.shift, 'ke_time', this.timelines[e_index].e_time)
                    }
                } else {
                    if (this.shift.e_time && this.timelines[s_index].number == 0 && this.timelines[e_index].number == 0) {
                        if (this.shift.e_time < this.timelines[s_index].s_time) {
                            this.spr_show = true
                            this.$set(this.shift, 'ks_time', this.shift.e_time)
                            this.$set(this.shift, 'ke_time', this.timelines[s_index].s_time)
                        }
                        if (this.shift.s_time > this.timelines[e_index].e_time) {
                            this.spr_show = true
                            this.$set(this.shift, 'ks_time', this.timelines[e_index].e_time)
                            this.$set(this.shift, 'ke_time', this.shift.s_time)
                        }
                    }
                    if (!this.shift.e_time && (e_index - s_index) < 4) {
                        return
                    }
                    this.$set(this.shift, 's_time', this.shift.e_time ? Math.min(this.timelines[s_index].s_time, this.shift.s_time) : this.timelines[s_index].s_time)
                    this.$set(this.shift, 'e_time', this.shift.e_time ? Math.max(this.timelines[e_index].e_time, this.shift.e_time) : this.timelines[e_index].e_time)
                }

                if (this.shift.s_time >= 1440) {
                    this.$set(this.shift, 's_time', 1425)
                }
                this.changeTimeLines()
                this.$emit("setShift", this.getShift())
            },
            selMove(event, item, index) {
                if (this.is_mousedown) {
                    this.e_index = index
                    this.timelines.map((item, index) => {
                        if (index >= Math.min(this.s_index, this.e_index) && index <= Math.max(this.s_index, this.e_index)) {
                            this.$set(item, "selected", true)
                        } else {
                            this.$set(item, "selected", false)
                        }
                    })
                }
            },
            selOut() {
                if (this.is_mousedown) {
                    this.s_index = null
                    this.e_index = null
                    this.timelines.map((item, index) => {
                        this.$set(item, "selected", false)
                    })
                    this.is_mousedown = false
                }
            },
            view_google_map(index) {
                if (!this.staff_addresses[index].address) {
                    this.$message.warning("住所を入力してください.")
                    return false
                }
                let lat = this.staff_addresses[index].latitude ? this.staff_addresses[index].latitude : null
                let lng = this.staff_addresses[index].longitude ? this.staff_addresses[index].longitude : null

                let mapConfig = {
                    zoom: 12,
                    center: {lat: lat, lng: lng},
                    address: this.staff_addresses[index].address
                }
                this.$set(this, "mapConfig", mapConfig)
                this.$refs.refGoogleMap.initializeMap(mapConfig)
                this.setMapIndex = index
                this.google_map_modal = true
            },
            setPosition(position) {
                if (position) {
                    this.staff_addresses[this.setMapIndex].latitude = position.lat
                    this.staff_addresses[this.setMapIndex].longitude = position.lng
                }
                this.google_map_modal = false
            },
            closeMap() {
                this.google_map_modal = false
            },
            createAddress(val) {
                this.staff_addresses.push({
                    address: null,
                    latitude: null,
                    longitude: null,
                    field_id: this.shift.field_id,
                    required_time: null,
                    email_time: null
                })
            },
            deleteAddress(index) {
                if (this.staff_addresses.length > 1) {
                    this.staff_addresses.splice(index, 1)
                }
            },
            getStaffAddress() {
                if (!this.shift.staff_id) return;
                let params = {
                    "staff_id": this.shift.staff_id,
                    "field_id": this.shift.field_id,
                }
                getStaffAddress(params).then(res => {
                    if (res.data.status == "success") {
                        this.staff_addresses = res.data.result
                        if(!this.staff_addresses || this.staff_addresses.length == 0){
                            this.createAddress()
                        }
                        this.edit_address = true
                    }
                })
            },
            updateStaffAddress() {
                if (!this.shift.staff_id) return;
                for (let i = 0; i < this.staff_addresses.length; i++) {
                    let saddr = this.staff_addresses[i]
                    if (!saddr.address || !saddr.latitude || !saddr.longitude || !saddr.field_id || !saddr.required_time || !saddr.email_time) {
                        this.$message.warning("出発先住所を入力してください")
                        return
                    }
                }
                this.address_loading = true
                let params = {
                    "staff_id": this.shift.staff_id,
                    "field_id": this.shift.field_id,
                    staff_address_list: this.staff_addresses,
                }
                updateStaffAddress(params).then((res) => {
                    this.address_loading = false
                    if (res.data.status == "success") {
                        this.edit_address = false
                    }
                })
            },
            toCancel(){
                this.edit_address = false
            }
        },
        mounted() {
            this.timelines = []
            let start = 0
            let end = this.end_hour * 60
            for (let t = start; t < end; t += 15) {
                let number = 0
                if (t >= this.s_time && (t + 15) <= this.e_time) {
                    number = 1
                }
                if (t >= this.ks_time && (t + 15) <= this.ke_time) {
                    number = 2
                }
                this.timelines.push({
                    s_time: t,
                    e_time: t + 15,
                    number: number,
                    selected: false
                })
            }

            this.hours_list = []
            for (let i = 0; i <= this.end_hour; i++) {
                this.hours_list.push(i)
            }

            this.minutes_list = []
            for (let i = 0; i < 60; i += 15) {
                this.minutes_list.push(i)
            }

            let listEl = document.getElementById('list');
            this.listWidth = listEl.clientWidth;
            this.listHeight = listEl.clientHeight;
            window.addEventListener('resize', () => {
                this.listWidth = listEl.clientWidth;
                this.listHeight = listEl.clientHeight;
            })

            this.createAddress()
        },
        computed: {
            s_time() {
                return this.shift.s_time
            },
            e_time() {
                return this.shift.e_time
            },
            ks_time() {
                return this.shift.ks_time
            },
            ke_time() {
                return this.shift.ke_time
            },
            sp_e() {
                return this.e_time ? this.$utils.Datetimes.num2hi(this.e_time) : ''
            },
            sp_s() {
                return this.e_time ? this.$utils.Datetimes.num2hi(this.s_time) : ''
            },
            spr_e() {
                return this.ke_time ? this.$utils.Datetimes.num2hi(this.ke_time) : ''
            },
            spr_s() {
                return this.ke_time ? this.$utils.Datetimes.num2hi(this.ks_time) : ''
            },
            sel_sh: {
                get() {
                    return parseInt(this.sel_s / 60)
                },
                set(v) {
                    this.sel_s = v * 60 + this.sel_sm
                }
            },
            sel_sm: {
                get() {
                    return parseInt(this.sel_s % 60)
                },
                set(v) {
                    this.sel_s = this.sel_sh * 60 + v
                }
            },
            sel_eh: {
                get() {
                    return parseInt(this.sel_e / 60)
                },
                set(v) {
                    this.sel_e = v * 60 + this.sel_em
                }

            },
            sel_em: {
                get() {
                    return parseInt(this.sel_e % 60)
                },
                set(v) {
                    this.sel_e = this.sel_eh * 60 + v
                }
            },

        },
        created() {

        }
    }
</script>

<style scoped>
    .s-layout {
        position: relative;
        width: 100%;
        height: auto;
    }

    .s-content {
        position: relative;
        top: 0;
        left: 0;
        width: 100%;
        border: solid 1px #666;
        user-select: none;
    }

    .s-element {
        width: 100%;
        height: 20px;
        cursor: pointer;
    }

    .s-element.m {
        border-top: 1px solid #ddd;
    }

    .s-element.bm {
        border-top: 1px solid #999 !important;
    }

    .s-element.selected, .drag-m {
        background-color: #fdc7ff !important;
    }

    .drag {
        height: 20px;
        padding-top: 2px;
        width: 100%;
        background-color: #f8f7a5;
        cursor: pointer;
        font-size: 11px;
    }

    .drag-r {
        height: 20px;
        padding-top: 2px;
        width: 100%;
        background-color: #dfffe7;
        cursor: pointer;
        font-size: 11px;
    }

    .icon-close, .icon-edit, .icon-home {
        font-size: 14px;
    }

    .icon-close:hover {
        font-weight: bold;
        font-size: 18px;
        color: red
    }

    .icon-edit:hover {
        font-size: 18px;
        color: #0065ff
    }

    .icon-home:hover {
        font-size: 18px;
        color: #ce03ff
    }

    .address-table {
        border-collapse: collapse;
        text-align: center;
    }

    .address-table th {
        padding: 8px;
        border: 1px solid #999;
    }

    .address-table tr {
        border: 1px solid #ccc;
        border-radius: 2px;
    }

    .address-table td {
        padding: 8px 5px;
        border: 1px solid #ccc;
    }
</style>