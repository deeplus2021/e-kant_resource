<template>
    <Card>
        <h3 slot="title" style="margin-left: 10px;">{{field_info.name}}:配置ポスト表 <span style="padding-left: 30px;">{{field_info.address}}</span><span style="padding-left: 30px;">電話番号:{{field_info.tel}}</span></h3>
        <Form :model="formItem" ref="refFormItem" :rules="rule_form" :label-width="120" label-position="right">
            <Row :gutter="16">
                <Col span="24">
                    <FormItem label="曜日/特別日" prop="date_type">
                        <RadioGroup v-model="date_type">
                            <Radio :label="0">該当曜日</Radio>
                            <Radio :label="1">特別日</Radio>
                        </RadioGroup>
                    </FormItem>
                </Col>
            </Row>
            <Row :gutter="16">
                <Col span="12">
                    <FormItem label="該当曜日" prop="p_weeks" v-if="date_type===0">
                        <Select v-model="formItem.p_weeks" multiple style="width:300px">
                            <Option v-for="(option, index) in weeks" :value="index+1" :key="index">{{option}}</Option>
                        </Select>
                    </FormItem>
                </Col>
                <Col span="24">
                    <FormItem label="開始日~終了日" prop="se_date" v-if="date_type===1">
                        <DatePicker v-model="formItem.se_date" :options="disabled_date_option"  type="daterange" placement="bottom-end" placeholder="Select date"></DatePicker>
                    </FormItem>
                </Col>
            </Row>
            <Row :gutter="16">
                <FormItem label="時間帯">
                    <div class="table-post-box" :style="{'height':Height + 'px'}">
                        <table border class="table-post">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">時間帯</th>
                                    <th style="width: 200px;">配置人数</th>
                                </tr>
                            </thead>
                            <tbody @mouseleave="selOut">
                                <tr v-for="(item, index) in formItem.post_times" :key="index"
                                    :class="{'selected':item.selected}"
                                    @mousedown="selDown(item,index)"
                                    @mouseup="selUp(item,index)"
                                    @mousemove="selMove(item,index)"
                                    :style="{'background-color': 'rgb(' + (255 - item.number * 10) + ',255,255)'}"
                                >
                                    <td>{{$utils.Datetimes.num2hi(item.s_time)}} - {{$utils.Datetimes.num2hi(item.e_time)}}</td>
                                    <td>{{item.number}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </FormItem>
            </Row>
            <Row :gutter="16">
                <Col span="24">
                    <FormItem label="コピー登録" prop="special_date_list">
                        <DatePicker v-model="special_date" :options="add_disabled_date_option" type="daterange" placement="bottom-end" placeholder="Select date range" @on-change="addSpecialDate"></DatePicker>
                        <br/>
                        <Tag v-for="(item, index) in special_date_list" closable @on-close="deleteSpacialDate(index)" :key="index">{{item.s_date == item.e_date ? item.s_date : item.s_date + "~" + item.e_date}}</Tag>
                    </FormItem>

                </Col>
            </Row>
        </Form>
        <Row :gutter="16">
            <Col span="12" style="text-align: right">
                <Button type="primary" size="large" @click="submit" :loading="submitLoading" style="width: 120px;">登 録</Button>
            </Col>
            <Col span="12">
                <Button type="info" size="large"  @click="toCancel">キャンセル</Button>
            </Col>
        </Row>
        <Modal v-model="editNumberModal" title="配置人数" @on-ok="setNumber" @on-cancel="cancelNumber" width="350">
            <div id="idPostNumber" style="text-align: center">
                <InputNumber v-model="input_number" :min="0" @keyup.enter.native="setNumber"></InputNumber>
            </div>
        </Modal>
    </Card>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex'
    import {
        addPost,
        updatePost,
        getStaffList,
    } from '@/api/post_master'

    export default {
        props:["field_info"],
        data() {
            return {
                field_id: null,
                weeks: ['月', '火', '水', '木', '金', '土', '日'],
                field: {},
                date_type: 0,
                formItem: {
                    id: null,
                    field_id: this.field_id,
                    p_weeks: [],
                    se_date: [],
                    post_times: [],
                    selected_date_list:[],
                },
                special_date_list:[],
                special_date:[],
                loading: false,
                submitLoading: false,
                status: '登録',
                rule_form: {
                    p_weeks: [{required: true, type: 'array', message: "該当曜日は必須です", trigger: 'change'}],
                    se_date: [
                        {required: true, type: 'array', message: "開始日~終了日は必須です", trigger: 'change'},
                    ],
                },
                is_mousedown: false,
                s_index:0,
                e_index:0,
                input_number: 0,
                editNumberModal: false,
            }
        },
        methods: {
            submit() {
                if (this.date_type === 1) {
                    this.rule_form = {
                        se_date: [{required: true, type: 'array', message: "開始日~終了日は必須です", trigger: 'change'}],
                    }
                } else {
                    this.rule_form = {
                        p_weeks: [{required: true, type: 'array', message: "該当曜日は必須です", trigger: 'change'}],
                    }
                }
                this.$refs['refFormItem'].validate((valid) => {
                    if (valid) {
                        this.submitLoading = true
                        const params = {
                            id: this.formItem.id,
                            field_id: this.formItem.field_id,
                            p_weeks: this.date_type === 1 ? null : this.formItem.p_weeks,
                            s_date: this.date_type === 1 ? this.$utils.Datetimes.getymd(this.formItem.se_date[0]) : null,
                            e_date: this.date_type === 1 ? this.$utils.Datetimes.getymd(this.formItem.se_date[1]) : null,
                            post_times: this.formItem.post_times,
                            special_date_list: this.special_date_list
                        }
                        if (this.formItem.id) {
                            updatePost(params).then((res) => {
                                this.submitLoading = false
                                if (res.data.status == "success") {
                                    this.$Message.success('成功');
                                    this.toCancel(1)
                                }
                            }).catch((error) => {this.submitLoading = false})
                        } else {
                            addPost(params).then((res) => {
                                this.submitLoading = false
                                if (res.data.status == "success") {
                                    this.$Message.success('成功');
                                    this.toCancel(1)
                                }
                            }).catch((error) => {this.submitLoading = false})
                        }
                    }
                })
            },
            toCancel(data) {
                this.$emit('toCancel', data)
            },
            showPost(data, field_id = null,) {
                let _this = this
                _this.field_id = field_id
                _this.status = '登録'
                _this.formItem = {
                    id: null,
                    field_id: _this.field_id,
                    p_weeks: [],
                    se_date: [],
                    post_times: [],
                    selected_date_list:[],
                }
                this.special_date_list = []
                this.special_date = []
                let start = 0
                let end = 2160
                for (let t = start; t < end; t += 15) {
                    _this.formItem.post_times.push({
                        s_time: t,
                        e_time: t + 15,
                        number: 0,
                        selected: false
                    })
                }
                if (!field_id) {
                    _this.status = '編集'
                    _this.formItem.id = data.id
                    _this.formItem.field_id = data.field_id
                    _this.formItem.p_weeks = [data.p_week]
                    if(data.s_date && data.e_date){
                        _this.formItem.se_date = [this.$utils.Dates.str2Date(data.s_date), this.$utils.Dates.str2Date(data.e_date)]
                    }
                    _this.date_type = data.p_week ? 0 : 1
                    _this.formItem.post_times = data.post_times
                }
                _this.formItem.selected_date_list = data.selected_date_list
            },
            selDown(item, index){
                this.is_mousedown = true
                this.$set(item, "selected", true)
                this.s_index = index
            },
            setNumber(){
                for(let i=Math.min(this.s_index,this.e_index); i<=Math.max(this.s_index,this.e_index);i++){
                    this.$set(this.formItem.post_times[i], "number", this.input_number)
                    this.$set(this.formItem.post_times[i], "selected", false)
                }
                this.editNumberModal = false
            },
            cancelNumber(){
                this.formItem.post_times.map((item, index)=>{
                    this.$set(item, "selected", false)
                })
            },
            selUp(item, index){
                if(!this.is_mousedown) return
                this.is_mousedown = false
                this.e_index = index
                let _this = this
                _this.input_number = 0
                this.editNumberModal = true

                setTimeout(()=>{
                    document.getElementById('idPostNumber').getElementsByTagName('input')[0].focus();
                    }, 300)
            },
            selMove(item, index){
                if(this.is_mousedown){
                    this.e_index = index
                    this.formItem.post_times.map((item, index)=>{
                        if(index >= Math.min(this.s_index,this.e_index) && index <= Math.max(this.s_index,this.e_index) ){
                            this.$set(item, "selected", true)
                        }
                        else{
                            this.$set(item, "selected", false)
                        }
                    })
                }
            },
            selOut(){
                if(this.is_mousedown){
                    this.s_index = null
                    this.e_index = null
                    this.formItem.post_times.map((item, index)=>{
                        this.$set(item, "selected", false)
                    })
                    this.is_mousedown = false
                }
            },
            addSpecialDate(){
                if(this.special_date.length > 1){
                    let disabled = false;
                    for(let i = this.special_date[0].getTime(); i<=this.special_date[1].getTime();i+=86400000){
                        disabled = this.add_disabled_date_option.disabledDate(i)
                        if(disabled){
                            break;
                        }
                    }
                    if(!disabled){
                        this.special_date_list.push({
                            s_date: this.$utils.Datetimes.getymd(this.special_date[0]),
                            e_date: this.$utils.Datetimes.getymd(this.special_date[1]),
                        })
                    }
                }
                setTimeout(()=>{
                    this.special_date = []
                },300)
            },
            deleteSpacialDate(index){
                this.special_date_list.splice(index,1)
            }
        },
        mounted() {

        },
        created() {

        },
        watch: {},
        computed: {
            ...mapGetters(["screenHeight"]),
            Height() {
                return parseInt(this.screenHeight - 350)
            },
            disabled_date_option() {
                 return {
                     disabledDate:(date) =>  {
                        if(date){
                            for (let i=0;i<this.formItem.selected_date_list.length;i++) {
                                const one = this.formItem.selected_date_list[i]
                                if(date.valueOf() >= this.$utils.Dates.str2Date(one.s_date).getTime() && date.valueOf() <= this.$utils.Dates.str2Date(one.e_date).getTime()){
                                    return true
                                }
                            }
                            for (let i=0;i<this.special_date_list.length;i++) {
                                const one = this.special_date_list[i]
                                if(date.valueOf() >= this.$utils.Dates.str2Date(one.s_date).getTime() && date.valueOf() <= this.$utils.Dates.str2Date(one.e_date).getTime()){
                                    return true
                                }
                            }
                        }
                        return false;
                    }
                 }

            },
            add_disabled_date_option(){
                return {
                    disabledDate: (date) => {
                        if (date) {
                            for (let i=0;i<this.formItem.selected_date_list.length;i++) {
                                const one = this.formItem.selected_date_list[i]
                                if (date.valueOf() >= this.$utils.Dates.str2Date(one.s_date).getTime() && date.valueOf() <= this.$utils.Dates.str2Date(one.e_date).getTime()) {
                                    return true
                                }
                            }
                            for (let i=0;i<this.special_date_list.length;i++) {
                                const one = this.special_date_list[i]
                                if (date.valueOf() >= this.$utils.Dates.str2Date(one.s_date).getTime() && date.valueOf() <= this.$utils.Dates.str2Date(one.e_date).getTime()) {
                                    return true
                                }
                            }
                            if (this.formItem.se_date.length > 1 && this.formItem.se_date[0] && this.formItem.se_date[1]){
                                if (date.valueOf() >= this.formItem.se_date[0].getTime() && date.valueOf() <= this.formItem.se_date[1].getTime()) {
                                    return true
                                }
                            }
                        }
                        return false;
                    }
                }
            }
        }
    }
</script>

<style scoped>
    .table-post-box{
        width: 100%;
        padding-left: 30px;
        overflow-y: scroll;
    }
    .table-post{
        text-align: center;
        border-spacing: 0;
        user-select: none;
        font-size: 14px;
        font-weight: 400;
    }
    .table-post>thead>tr>th{
        background-color: #fff;
        top: 0;
        position: sticky;
        position: -webkit-sticky;
        z-index: 2;
    }
    .table-post>tbody>tr{
        line-height: 18px;
    }
    .table-post>tbody>tr>td{
        cursor: pointer;
    }
    .table-post>tbody>tr.selected>td{
        background-color: #fdc7ff!important;
    }
</style>
