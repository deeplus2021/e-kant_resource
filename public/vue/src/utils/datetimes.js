import ca from "element-ui/src/locale/lang/ca";

export default {
//获取年月日
    getymd(date) {
        if (date != '') {
            var y = date.getFullYear();
            var m = date.getMonth() + 1;
            m = m < 10 ? '0' + m : m;
            var d = date.getDate();
            d = d < 10 ? ('0' + d) : d;
            return y + '-' + m + '-' + d;
        } else {
            return '';
        }
    },
    getymd_locale(date) {
        if (date != '') {
            var y = date.getFullYear();
            var m = date.getMonth() + 1;
            m = m < 10 ? '0' + m : m;
            var d = date.getDate();
            d = d < 10 ? ('0' + d) : d;
            return y + '年' + m + '月' + d + '日';
        } else {
            return '';
        }
    },
    getym(date) {
        if (date != '') {
            var y = date.getFullYear();
            var m = date.getMonth() + 1;
            m = m < 10 ? '0' + m : m;
            return y + '-' + m;
        } else {
            return '';
        }

    },
    getmd(date) {
        if (date != '') {
            var m = date.getMonth() + 1;
            var d = date.getDate();
            return m + '/' + d;
        } else {
            return '';
        }
    },
    //day of week
    getdw(date) {
        if (date != '') {
            var weeks = ["日", "月", "火", "水", "木", "金", "土"]
            return weeks[date.getDay()];
        } else {
            return '';
        }

    },
//获取年月日时分秒
    getymdhis(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        m = m < 10 ? ('0' + m) : m;
        var d = date.getDate();
        d = d < 10 ? ('0' + d) : d;
        var h = date.getHours();
        h = h < 10 ? ('0' + h) : h;
        var minute = date.getMinutes();
        minute = minute < 10 ? ('0' + minute) : minute;
        var second = date.getSeconds();
        second = second < 10 ? ('0' + second) : second;
        return y + '-' + m + '-' + d + ' ' + h + ':' + minute + ':' + second;
    },
//获取时分秒
    gethis(date) {
        var h = date.getHours();
        h = h < 10 ? ('0' + h) : h;
        var minute = date.getMinutes();
        minute = minute < 10 ? ('0' + minute) : minute;
        var second = date.getSeconds();
        second = second < 10 ? ('0' + second) : second;
        return h + ':' + minute + ':' + second;
    },
    gethi(date) {
        var h = date.getHours();
        h = h < 10 ? ('0' + h) : h;
        var minute = date.getMinutes();
        minute = minute < 10 ? ('0' + minute) : minute;
        return h + ':' + minute;
    },

//判断是否为字符串型时间
    isDateString(sDate) {
        var mp = /\d{4}-\d{2}-\d{2}/;
        sDate = sDate.toString();
        var matchArray = sDate.match(mp);
        if (matchArray == null) return false;
        var iaMonthDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        var iaDate = new Array(3);
        var year, month, day;
        iaDate = sDate.split("-");
        year = parseFloat(iaDate[0])
        month = parseFloat(iaDate[1])
        day = parseFloat(iaDate[2])
        if (year < 1900 || year > 2100) return false;
        if (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0)) iaMonthDays[1] = 29;
        if (month < 1 || month > 12) return false;
        if (day < 1 || day > iaMonthDays[month - 1]) return false;
        return true;
    },
//获取特定时间
    getTime(n) {
        var now = new Date();
        now.setTime(now.getTime() + 24 * 60 * 60 * 1000 * n);
        return now;
    },
    getTimesString(st, et) {
        return this.num2hi(st) + "-" + this.num2hi(et)
    },
    num2hi(num) {
        try {
            let h = parseInt(num / 60)
            let i = num % 60
            return (h < 10 ? ('0' + h) : h) + ":" + (i < 10 ? ('0' + i) : i);
        } catch (e) {
            return "00:00"
        }
    },
    hi2num(hi) {
        try {
            let v = hi.split(":")
            return parseInt(v[0]) * 60 + parseInt(v[1])
        } catch (e) {
            return 0
        }
    },
    ymdhis2hi(str0, str){
        str = str.replace(' ', 'T')
        const s_datetime = (new Date(str0 + "T00:00:00")).getTime()
        const r_datetime =  (new Date(str)).getTime()
        let diff = parseInt((r_datetime - s_datetime) / (60 * 1000))
        const h = parseInt(diff / 60)
        const i = diff % 60
        return ((h < 10 && h >= 0) ? ('0' + h) : h) + ":" + ((i < 10 && i >= 0) ? ('0' + i) : i);
    },
    ymdhis2num(str0, str){
        str = str.replace(' ', 'T')
        const s_datetime = (new Date(str0 + "T00:00:00")).getTime()
        const r_datetime =  (new Date(str)).getTime()
        return parseInt((r_datetime - s_datetime) / (60 * 1000))
    }
}
