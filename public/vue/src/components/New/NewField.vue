<template>
    <Card>
        <h2 slot="title" style="margin-bottom: 20px;">現場{{status}}</h2>
        <Form :model="formItem" ref="refFormItem" :rules="rule_form" :label-width="120" label-position="right">
            <Row :gutter="16">
                <Col span="8">
                    <FormItem label="現場名" prop="name">
                        <Input v-model="formItem.name"></Input>
                    </FormItem>
                </Col>
                <Col span="8">
                    <FormItem label="ふりがな" prop="furigana">
                        <Input v-model="formItem.furigana"></Input>
                    </FormItem>
                </Col>
                <Col span="8">
                    <FormItem label="電話番号" prop="tel">
                        <Input v-model="formItem.tel"></Input>
                    </FormItem>
                </Col>
            </Row>
            <Row :gutter="16">
                <Col span="24">
                    <FormItem label="所在地" prop="address">
                        <Input v-model="formItem.address"></Input>
                    </FormItem>
                </Col>
            </Row>
            <Row :gutter="16">
                <Col span="10">
                    <FormItem label="緯度" prop="latitude">
                        <InputNumber v-model="formItem.latitude" style="width:150px;"></InputNumber>
                    </FormItem>
                </Col>
                <Col span="10">
                    <FormItem label="経度" prop="longitude">
                        <InputNumber v-model="formItem.longitude" style="width:150px;"></InputNumber>
                    </FormItem>
                </Col>
                <Col span="4">
                    <Button type="success" @click="view_google_map">地図表示</Button>
                </Col>
            </Row>
            <Row :gutter="16">
                <Col span="12">
                    <FormItem label="開始時間" prop="s_time">
                        <vTimeSelector v-model="formItem.s_time" :lte="60*24-1"></vTimeSelector>
                    </FormItem>
                </Col>
                <Col span="12">
                    <FormItem label="終了時間" prop="e_time">
                        <vTimeSelector v-model="formItem.e_time"></vTimeSelector>
                    </FormItem>
                </Col>
            </Row>
            <Row :gutter="16">
                <Col span="24">
                    <FormItem label="定休日" prop="holidays">
                        <m-date-picker v-model="formItem.holidays" :multi="true" :always-display="false"
                                       :format="format_date" :disp="lang_string"></m-date-picker>
                        <!--<DatePicker type="date"  v-model="formItem.holidays" multiple placeholder="Select date" style="width: 100%;"></DatePicker>-->
                    </FormItem>
                </Col>
            </Row>
            <Row :gutter="16">
                <Col span="24">
                    <FormItem label="マニアル等" inline>
                        <Upload
                                :before-upload="handleUpload"
                                :max-size="10240"
                                action="#"
                        >
                            <Button size="small" type="success">ファイル選択</Button>
                        </Upload>
                        <div>ファイル名:
                            <span v-if="files.length === 0">ファイルなし</span>
                        </div>
                        <div>
                            <ul class="file-list" v-for="(file,index) in files" :key="index">
                                <li><span class="file-select" @click="downloadFieldFile(file)" style="cursor: pointer;">{{ file.name }}</span>
                                    <Icon class="icon-close" size="20" type="ios-close" @click="delFileList(index)" style="cursor: pointer;"></Icon>
                                </li>
                            </ul>
                        </div>
                    </FormItem>
                </Col>
            </Row>
            <FormItem label="現場責任者" prop="cstaffs">
                <Select v-model="formItem.cstaffs" multiple filterable>
                    <Option v-for="(option, index) in staff_list" :value="option.id" :key="option.id">{{option.name}}</Option>
                </Select>
            </FormItem>

            <FormItem label="スタッフ" prop="staffs">
                <Select v-model="formItem.staffs" multiple filterable>
                    <Option v-for="(option, index) in staff_list" :value="option.id" :key="option.id">{{option.name}}</Option>
                </Select>
            </FormItem>

            <FormItem label="緊急対応スタッフ" prop="estaffs">
                <Select v-model="formItem.estaffs" multiple filterable>
                    <Option v-for="(option, index) in staff_list" :value="option.id" :key="option.id">{{option.name}}</Option>
                </Select>
            </FormItem>
            <FormItem v-if="formItem.id">
                <Button type="success" @click="goPostMaster">配置ポスト登録ページへ</Button>
            </FormItem>
        </Form>
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
        <Modal v-model="post_list_modal" :mask-closable="false" :closable="true" :footer-hide="true" width="800" :styles="{top: '70px'}">
            <PostList ref="refPostList"></PostList>
        </Modal>
    </Card>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex'
    import {
        addField,
        updateField,
        uploadFiles,
        getStaffList,
        downloadFieldFile
    } from '@/api/field_master'
    import PostList from '@/components/New/PostList.vue'
    export default {
        components: {
            PostList,
        },
        data() {
            return {
                formItem: {
                    id: null,
                    name: '',
                    furigana: '',
                    tel: '',
                    address: '',
                    latitude: 0,
                    longitude: 0,
                    s_time: 0,
                    e_time: 0,
                    files: [],
                    holidays: [],
                    selected_files: [],
                    cstaffs: [],
                    staffs: [],
                    estaffs: [],
                    is_active: 1,
                },
                loading: false,
                status: '登録',
                files: [],
                uploaded_files: [],
                staff_list: [],
                rule_form: {
                    name: [
                        {required: true, message: '入力してください', trigger: 'blur'},
                        {pattern: "^[ぁ-んァ-ヶ一-龥々０-９ａ-ｚＡ-Ｚー・a-zA-Z0-9 　]+$", max: 32, message: '名前は全角半角文字列のみ有効です', trigger: 'blur'}
                    ],
                    furigana: [
                        {required: true, message: '入力してください', trigger: 'blur'},
                        {pattern: "^[あ-ん゛゜ぁ-ぉゃ-ょー「」、 　]+$", max: 64, message: 'ひだがなで入力してください', trigger: 'blur'}
                    ],
                    tel: [
                        {required: true, message: '入力してください', trigger: 'blur'},
                        {pattern: "^[0-9]+$", message: '半角数字で入力してください', trigger: 'blur'},
                    ],
                    address: [
                        {required: true, message: '入力してください', trigger: 'blur'},
                    ],
                    latitude: [
                        {required: true, message: '入力してください', trigger: 'blur', type: 'number'},
                    ],
                    longitude: [
                        {required: true, message: '入力してください', trigger: 'blur', type: 'number'},
                    ],
                    s_time: [
                        {required: true, message: '入力してください', trigger: 'blur', type: 'number'},
                        {validator: (rule, value, callback) => {
                                if (value > this.formItem.e_time) {
                                    callback(new Error('開始時間は、終了時間以下でなければなりません。'));
                                } else {
                                    callback();
                                }
                            }, trigger: 'change'}
                    ],
                    e_time: [
                        {required: true, message: '入力してください', trigger: 'blur', type: 'number'},
                        {validator: (rule, value, callback) => {
                                if (value < this.formItem.s_time) {
                                    callback(new Error('終了時間は、開始時間以上でなければなりません。'));
                                } else {
                                    callback();
                                }
                            }, trigger: 'change'}
                    ],
                },
                lang_string: ["日", "月", "火", "水", "木", "金", "土", "年", "月", "Cancel", "OK"],
                google_map_modal: false,
                mapConfig: {},
                post_list_modal: false
            }
        },
        methods: {
            selectTime(value) {
                console.log(value)
                this.$set(this.formItem, "e_time", value)
            },
            submit() {
                this.$refs['refFormItem'].validate((valid) => {
                    if (valid) {
                        this.loading = true
                        const params = {
                            id: this.formItem.id,
                            name: this.formItem.name,
                            furigana: this.formItem.furigana,
                            tel: this.formItem.tel,
                            address: this.formItem.address,
                            latitude: this.formItem.latitude,
                            longitude: this.formItem.longitude,
                            s_time: this.formItem.s_time,
                            e_time: this.formItem.e_time,
                            holidays: [],
                            files: [],
                            cstaffs: this.formItem.cstaffs,
                            staffs: this.formItem.staffs,
                            estaffs: this.formItem.estaffs,
                            is_active: this.formItem.is_active,

                        }
                        if (this.formItem.holidays.length > 0) {
                            let holidays = []
                            for (let i = 0; i < this.formItem.holidays.length; i++) {
                                holidays.push(this.$utils.Datetimes.getymd(this.formItem.holidays[i]))
                            }
                            params.holidays = holidays
                        }

                        if (this.files.length > 0) {
                            params.files = this.files.map((item) => {
                                return item.id
                            })
                        }
                        if (this.formItem.id) {
                            updateField(params).then((res) => {
                                this.loading = false
                                if (res.data.status == "success") {
                                    this.$Message.success('成功');
                                    this.toCancel(1)
                                }
                            }).catch((error) => {this.loading = false})
                        } else {
                            addField(params).then((res) => {
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
            showField(data = null) {
                let _this = this
                _this.status = '登録'
                _this.files = []
                _this.formItem = {
                    id: null,
                    name: '',
                    furigana: '',
                    tel: '',
                    address: '',
                    latitude: 0,
                    longitude: 0,
                    s_time: 0,
                    e_time: 0,
                    files: [],
                    holidays: [],
                    selected_files: [],
                    uploaded_files: [],
                    cstaffs: [],
                    staffs: [],
                    estaffs: [],
                    is_active: 1,
                }
                if (data) {
                    _this.status = '編集'
                    _this.formItem.id = data.id
                    _this.formItem.name = data.name
                    _this.formItem.furigana = data.furigana
                    _this.formItem.tel = data.tel
                    _this.formItem.address = data.address
                    _this.formItem.latitude = data.latitude
                    _this.formItem.longitude = data.longitude
                    _this.formItem.s_time = data.s_time
                    _this.formItem.e_time = data.e_time
                    _this.formItem.files = data.files
                    _this.files = data.files
                    if (data.holidays.length > 0) {
                        let holidays = []
                        for (let i = 0; i < data.holidays.length; i++) {
                            holidays.push(_this.$utils.Dates.str2Date(data.holidays[i].h_date))
                        }
                        _this.formItem.holidays = holidays
                    }
                    _this.staff_list = data.staff_list
                    _this.formItem.cstaffs = data.cstaffs
                    _this.formItem.staffs = data.staffs
                    _this.formItem.estaffs = data.estaffs
                    _this.formItem.is_active = data.is_active
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
            view_google_map() {
                let lat = this.formItem.latitude ? this.formItem.latitude : null
                let lng = this.formItem.longitude ? this.formItem.longitude : null
                let mapConfig = {
                    zoom: 12,
                    center: {lat: lat, lng: lng},
                    address: this.formItem.address
                }
                this.$set(this, "mapConfig", mapConfig)
                this.$refs.refGoogleMap.initializeMap(mapConfig)
                this.google_map_modal = true
            },
            setPosition(position) {
                if (position) {
                    this.formItem.latitude = position.lat
                    this.formItem.longitude = position.lng
                }
                this.google_map_modal = false
            },
            closeMap() {
                this.google_map_modal = false
            },
            goPostMaster() {
                this.$refs.refPostList.showPostList(this.formItem)
                this.post_list_modal = true
            },
            handleUpload(file) {
                let formData = new FormData
                formData.append("files[0]", file)
                uploadFiles(formData).then(res => {
                    if (res.data.status == "success" && res.data.result) {
                        res.data.result.map((item) => {
                            this.files.push(item)
                        })
                    }
                })
                return false
            },
            delFileList(index) {
                let that = this;
                that.files.splice(index, 1);
            },
            getStaffList(){
                getStaffList().then(res => {
                    if (res.data.status == "success") {
                        this.staff_list = res.data.result
                    }
                })
            },
            downloadFieldFile(file){
                let params = {
                    file_id: file.id,
                }
                downloadFieldFile(params).then((response)=>{
                    let fileURL = window.URL.createObjectURL(new Blob([response.data]));
                    let fileLink = document.createElement('a');
                    fileLink.href = fileURL;
                    fileLink.setAttribute('download', file.name);
                    document.body.appendChild(fileLink);
                    fileLink.click();
                })
            }
        },
        mounted() {

        },
        created() {

        },
        watch: {},
        computed: {}
    }
</script>

<style scoped>
    .icon-close:hover{
        font-weight: bold;
        font-size: 18px;
        color:red
    }
    .file-select:hover{
        font-weight: bold;
        color: #a600ff
    }
</style>
