<template>
    <Card>
        <h2 slot="title" style="margin-bottom: 20px;">スタッフ{{status}}</h2>
        <Form :model="formItem" ref="refFormItem" :rules="rule_form" :label-width="120" label-position="right">
            <Row :gutter="16">
                <Col span="8">
                    <FormItem label="名前" prop="name">
                        <Input v-model="formItem.name" maxlength="32"></Input>
                    </FormItem>
                </Col>
                <Col span="8">
                    <FormItem label="ふりがな" prop="furigana" maxlength="64">
                        <Input v-model="formItem.furigana"></Input>
                    </FormItem>
                </Col>
                <Col span="8">
                    <FormItem label="社員コード" prop="code" maxlength="32">
                        <Input v-model="formItem.code"></Input>
                    </FormItem>
                </Col>
            </Row>
            <Row :gutter="16">
                <Col span="8">
                    <FormItem label="メールアドレス" prop="email">
                        <Input v-model="formItem.email"></Input>
                    </FormItem>
                </Col>
                <Col span="8" v-if="view_password">
                    <FormItem label="パスワード" prop="password">
                        <Input v-model="formItem.password"></Input>
                    </FormItem>
                </Col>
                <Col span="8">
                    <FormItem label="電話番号" prop="tel">
                        <Input v-model="formItem.tel" maxlength="11"></Input>
                    </FormItem>
                </Col>
            </Row>
            <Row :gutter="16">
                <Col span="24">
                    <FormItem label="原則休日登録日" prop="holiday">
                        <CheckboxGroup v-model="formItem.holiday">
                            <Checkbox v-for="(item, index) in holidays" :label="index+1" :key="index">{{item}}
                            </Checkbox>
                        </CheckboxGroup>
                    </FormItem>
                </Col>
            </Row>
            <template v-if="roles === 1 || roles === 2">
            <Row :gutter="16">
                <Col span="24">
                    <FormItem label="希望休日" prop="desired_holiday">
                        <m-date-picker v-model="formItem.desired_holiday" :multi="true" :always-display="false"
                                       :format="format_date" :disp="lang_string"></m-date-picker>
                    </FormItem>
                </Col>
            </Row>
            <Row :gutter="16">
                <Col span="24" v-if="view_password">
                    <FormItem label="権限" prop="staff_role_id">
                        <RadioGroup v-model="formItem.staff_role_id">
                            <Radio v-for="(item, index) in staff_role_list" :label="item.id" :key="item.id" v-if="!(roles===2 && item.id===1)">{{item.name}}</Radio>
                        </RadioGroup>
                    </FormItem>
                </Col>
                <Col span="24" v-else>
                    <FormItem label="権限" prop="staff_role_id">
                        <p v-for="(item, index) in staff_role_list" v-if="formItem.staff_role_id == item.id">{{item.name}}</p>
                    </FormItem>
                </Col>
            </Row>
            <Row :gutter="16">
                <Col span="12">
                    <FormItem label="前日確認メール" prop="yesterday_flag">
                        <RadioGroup v-model="formItem.yesterday_flag">
                            <Radio :label="1">送信する</Radio>
                            <Radio :label="0">送信しない</Radio>
                        </RadioGroup>
                    </FormItem>
                </Col>
                <Col span="12">
                    <FormItem label="当日確認メール" prop="today_flag">
                        <RadioGroup v-model="formItem.today_flag">
                            <Radio :label="1">送信する</Radio>
                            <Radio :label="0">送信しない</Radio>
                        </RadioGroup>
                    </FormItem>
                </Col>
            </Row>
            </template>

            <Row :gutter="16" v-if="formItem.id">
                <FormItem label="出発先住所選択" style="margin-bottom: 0">
                    <Button type="info" @click="createAddress"><Icon type="ios-add" size="18"/></Button>
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
                                    <InputNumber v-model="item.latitude" style="width: 100px;"></InputNumber><br/>
                                </td>
                                <td>
                                    <InputNumber v-model="item.longitude" style="width: 100px;"></InputNumber><br/>
                                </td>
                                <td>
                                    <Button type="success" @click="view_google_map(index)" size="small" style="width: 50px;">地図</Button>
                                </td>
                                <td>
                                    <Select v-model="item.field_id" style="width: 200px;">
                                        <Option v-for="(option, idx) in staff_fields" :value="option.id" :key="option.id">{{option.name}}</Option>
                                    </Select>
                                </td>
                                <td>
                                    <vTimeSelector v-model="item.required_time"></vTimeSelector>
                                </td>
                                <td>
                                    <Select v-model="item.email_time" style="width: 65px;">
                                        <Option v-for="(val, index) in [0, 15, 30, 45, 60, 75, 90, 105, 120]" :value="val" :key="index">{{val}}</Option>
                                    </Select>
                                </td>
                                <td>
                                    <Button type="error" size="small" @click="deleteAddress(index)"><Icon type="ios-trash" /></Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </Col>
            </Row>
        </Form>
        <br/>
        <Row :gutter="16">
            <Col span="12" style="text-align: right">
                <Button type="primary" size="large" @click="submit" :loading="loading" style="width: 120px;">登 録</Button>
            </Col>
            <Col span="12">
                <Button type="info" size="large"  @click="toCancel">キャンセル</Button>
            </Col>
        </Row>
        <Modal v-model="google_map_modal" :mask-closable="false" :closable="true" :footer-hide="true" width="800" :styles="{top: '70px'}">
            <vGoogleMap ref="refGoogleMap" @setPosition="setPosition" @closeMap="closeMap"></vGoogleMap>
        </Modal>
    </Card>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex'
    import {
        addStaff,
        updateStaff
    } from '@/api/staff_master'
    export default {
        components: {},
        data() {
            return {
                holidays: ['月', '火', '水', '木', '金', '土', '日', '祝日'],//TODO
                lang_string: ["日", "月", "火", "水", "木", "金", "土", "年", "月", "Cancel", "OK"],
                field_list: [],
                formItem: {
                    id: null,
                    code: '',
                    name: '',
                    furigana: '',
                    email: '',
                    tel: '',
                    staff_role_id: 3,
                    password: '',
                    yesterday_flag: 1,
                    today_flag: 1,
                    holiday: [6,7,8],
                    desired_holiday: [],
                    is_active: 1,
                },
                loading: false,
                status: '登録',
                files: [],
                staff_fields: [],
                rule_form: {
                    name: [
                        {required: true, message: '入力してください', trigger: 'blur'},
                        {pattern: "^[ぁ-んァ-ヶ一-龥々０-９ａ-ｚＡ-Ｚー・a-zA-Z0-9 　]+$", max: 32, message: '名前は全角半角文字列のみ有効です', trigger: 'blur'}
                    ],
                    furigana: [
                        {required: true, message: '入力してください', trigger: 'blur'},
                        {pattern: "^[あ-ん゛゜ぁ-ぉゃ-ょー「」、 　]+$", max: 64, message: 'ひだがなで入力してください', trigger: 'blur'}
                    ],
                    code: [
                        {required: true, message: '入力してください', trigger: 'blur'},
                        // {type: 'string', max: 32, message: '', trigger: 'blur'}
                    ],
                    email: [
                        {required: true, message: '入力してください', trigger: 'blur'},
                        {type: 'email', max: 128, message: 'メールアドレスの書式のみ有効です', trigger: 'blur'}
                    ],
                    tel: [
                        {required: true, message: '入力してください', trigger: 'blur'},
                        {pattern: "^[0-9]+$", message: '半角数字で入力してください', trigger: 'blur'},
                    ],
                    password: [
                        {required: true, message: '入力してください', trigger: 'blur'},
                        {pattern: "^[a-zA-Z0-9!#$%&*+,.:;=?@_-]+$", message:'半角英数字と記号を入力してください。', trigger: 'blur'}
                    ],
                },
                google_map_modal: false,
                staff_addresses: [],
                view_password: true,
                setMapIndex: 0,
            }
        },
        methods: {
            submit() {
                this.$refs['refFormItem'].validate((valid) => {
                    console.log(valid)
                    if (valid) {
                        if(this.formItem.id && this.roles !== 1){
                            for(let i=0;i<this.staff_addresses.length;i++){
                                let saddr = this.staff_addresses[i]
                                if(!saddr.address || !saddr.latitude || !saddr.longitude || !saddr.field_id || !saddr.required_time || !saddr.email_time){
                                    this.$message.warning("出発先住所を入力してください")
                                    return
                                }
                            }
                        }
                        const params = {
                            id: this.formItem.id,
                            email: this.formItem.email,
                            name: this.formItem.name,
                            code: this.formItem.code,
                            furigana: this.formItem.furigana,
                            tel: this.formItem.tel,
                            staff_role_id: this.formItem.staff_role_id,
                            password: this.formItem.password,
                            holiday: this.formItem.holiday,
                            desired_holiday: [],
                            yesterday_flag: this.formItem.yesterday_flag,
                            today_flag: this.formItem.today_flag,
                            staff_address_list: this.staff_addresses,
                            is_active: this.formItem.is_active,
                        }
                        if (this.formItem.desired_holiday.length > 0) {
                            let holidays = []
                            for (let i = 0; i < this.formItem.desired_holiday.length; i++) {
                                holidays.push(this.$utils.Datetimes.getymd(this.formItem.desired_holiday[i]))
                            }
                            params.desired_holiday = holidays
                        }
                        this.loading = true
                        if (this.formItem.id) {
                            updateStaff(params).then((res) => {
                                this.loading = false
                                if (res.data.status == "success") {
                                    this.$Message.success('成功');
                                    this.toCancel(1)
                                }
                            }).catch((error) => {this.loading = false})
                        } else {
                            addStaff(params).then((res) => {
                                this.loading = false
                                if (res.data.status == "success") {
                                    this.$Message.success('成功');
                                    this.toCancel(1)
                                }
                            }).catch((error) => {this.loading = false})
                        }
                    }
                })
            },
            toCancel(data) {
                this.$emit('toCancel', data)
            },
            showStaff(data = null, view_password = true) {
                this.view_password = view_password
                this.status = '登録'
                this.formItem = {
                    id: null,
                    code: '',
                    name: '',
                    furigana: '',
                    email: '',
                    tel: '',
                    staff_role_id: 3,
                    password: '',
                    yesterday_flag: 1,
                    today_flag: 1,
                    holiday: [6, 7, 8],
                    desired_holiday: [],
                    is_active: 1,
                }

                this.$set(this.rule_form.password[0], "required", true)
                this.staff_addresses = []
                if (data) {
                    this.status = '編集'
                    this.formItem.id = data.id
                    this.formItem.code = data.code
                    this.formItem.name = data.name
                    this.formItem.email = data.email
                    this.formItem.furigana = data.furigana
                    this.formItem.tel = data.tel
                    this.formItem.staff_role_id = data.staff_role_id
                    this.staff_addresses = data.staff_addresses
                    this.staff_fields = data.staff_field_list
                    this.formItem.yesterday_flag = data.yesterday_flag
                    this.formItem.today_flag = data.today_flag
                    this.formItem.holiday = data.holiday
                    if (data.desired_holiday && data.desired_holiday.length > 0) {
                        let holidays = []
                        for (let i = 0; i < data.desired_holiday.length; i++) {
                            holidays.push(this.$utils.Dates.str2Date(data.desired_holiday[i]))
                        }
                        this.formItem.desired_holiday = holidays
                    }
                    this.formItem.is_active = data.is_active
                    this.$set(this.rule_form.password[0], "required", false)
                    if(this.staff_addresses.length ==0){
                        this.createAddress()
                    }
                }

                this.$refs.refFormItem.resetFields();
            },
            format_date(date) {
                try {
                    return this.$utils.Datetimes.getymd(date)
                } catch (e) {
                    return date
                }
            },
            view_google_map(index) {
                if(!this.staff_addresses[index].address){
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
                    address : null,
                    latitude : null,
                    longitude : null,
                    field_id : null,
                    required_time : null,
                    email_time : null
                })
            },
            deleteAddress(index){
                if(this.staff_addresses.length > 1){
                    this.staff_addresses.splice(index, 1)
                }
            },
        },
        mounted() {
            this.createAddress()
        },
        created() {

        },
        watch: {},
        computed: {
            ...mapGetters([
                "roles",
                "staff_role_list",
            ]),
        }
    }
</script>

<style scoped>
    .address-table {
        border-collapse: collapse;
        text-align: center;
    }
    .address-table th{
        padding: 8px;
        border: 1px solid #999;
    }
    .address-table tr{
        border: 1px solid #ccc;
        border-radius: 2px;
    }
    .address-table td{
        padding: 8px 5px;
        border: 1px solid #ccc;
    }
</style>
