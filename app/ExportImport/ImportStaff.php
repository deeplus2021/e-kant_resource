<?php


namespace App\ExportImport;


use App\Models\Field;
use App\Models\Staff;
use App\Models\StaffAddress;
use App\Models\StaffField;
use App\Models\StaffRole;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

HeadingRowFormatter::default('none');

class ImportStaff implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation, ShouldQueue, WithCustomCsvSettings
{
    use Importable;
    public function __construct()
    {
        $this->headerStr = array(
            'code' => '社員コード',
            'email' => 'メールアドレス',
            'name' => '名前',
            'furigana' => 'ふりがな',
            'tel' => '電話番号',
            'staff_role_id' => '権限',
            'password' => 'パスワード',
            'yesterday_flag' => '前日確認メール',
            'today_flag' => '前日確認メール',
            'address' => '出発先住所',
            'latitude' => '緯度',
            'longitude' => '経度',
            'field_id' => '現場名',
            'required_time' => '現場までの時間(分)',
            'email_time' => '当日確認メール送信時間(分)',
        );
    }


    /**
     * @param array $row
     *
     * @return Model|Model[]|null
     */
    public function model(array $row)
    {
        $staff = new Staff();
        $staff->code = $row[$this->headerStr['code']];
        $staff->email = $row[$this->headerStr['email']];
        $staff->name = $row[$this->headerStr['name']];

        if(isset($row[$this->headerStr['furigana']]) && !empty($row[$this->headerStr['furigana']])){
            $staff->furigana = $row[$this->headerStr['furigana']];
        }

        if(isset($row[$this->headerStr['tel']]) && !empty($row[$this->headerStr['tel']])){
            $staff->tel = $row[$this->headerStr['tel']];
        }

        if(isset($row[$this->headerStr['staff_role_id']])) {
            $staff->staff_role_id = StaffRole::where('name', $row[$this->headerStr['staff_role_id']])->first()->id;
        }
        else{
            $staff->staff_role_id = config('constants.staff_roles.staff');
        }

        if(isset($row[$this->headerStr['password']]) && !empty($row[$this->headerStr['password']])){
            $staff->password = Hash::make($row[$this->headerStr['password']]);
        }
        else{
            $staff->password = Hash::make(config('constants.default_password'));
        }

        $staff->holiday = [6, 7, 8];
        $staff->is_active = 1;
        $staff->save();

        if(isset($row[$this->headerStr['address']]) && !empty($row[$this->headerStr['address']])){
            $staff_address = new StaffAddress();
            $staff_address->staff_id = $staff->id;
            $staff_address->address = $row[$this->headerStr['address']];
            $staff_address->latitude = (isset($row[$this->headerStr['latitude']]) && !empty($row[$this->headerStr['latitude']])) ? $row[$this->headerStr['latitude']] : 0;
            $staff_address->longitude = (isset($row[$this->headerStr['longitude']]) && !empty($row[$this->headerStr['longitude']])) ? $row[$this->headerStr['longitude']]: 0;
            $staff_address->field_id = Field::where('name', $row[$this->headerStr['field_id']])->first()->id;
            $staff_address->required_time = (isset($row[$this->headerStr['required_time']]) && !empty($row[$this->headerStr['required_time']])) ? $row[$this->headerStr['required_time']] : 30;
            $staff_address->email_time = (isset($row[$this->headerStr['email_time']]) && !empty($row[$this->headerStr['email_time']])) ? $row[$this->headerStr['email_time']] : 30;
            $staff_address->save();

            StaffField::updateOrCreate(
                [
                    "staff_id" => $staff->id,
                    "field_id" => Field::where('name', $row[$this->headerStr['field_id']])->first()->id
                ]
            );
        }
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        // TODO: Implement batchSize() method.
        return 100;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        // TODO: Implement chunkSize() method.
        return 100;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        $user = Auth::user();
        $in = "";
        if($user->staff_role_id != config('constants.staff_roles.super_admin'))
        {
            $in = DB::table("t_staff_field AS tsf")
                ->select(DB::raw("tf.name"))
                ->leftJoin("t_field AS tf", "tf.id", "tsf.field_id")
                ->where("staff_id", $user->id)
                ->get()->pluck("name")->toArray();
        }
        return [
            $this->headerStr['code'] => ['required'],
            $this->headerStr['email'] => ['required', 'email', 'unique:t_staff,email'],
            $this->headerStr['name'] => ['required', 'string', 'max:64', 'regex:/^[ぁ-んァ-ヶ一-龥々０-９ａ-ｚＡ-Ｚー・a-zA-Z0-9 　]+$/u'],
            $this->headerStr['furigana'] => ['nullable', 'string', 'max:64', 'regex:/^[あ-ん゛゜ぁ-ぉゃ-ょー「」、 　]+$/u'],
            $this->headerStr['tel']=> ['nullable', 'regex:/^[0-9]+$/', 'max:11'],
            $this->headerStr['staff_role_id'] => ['nullable', 'exists:t_staff_roles,name'],
            $this->headerStr['password'] => ['nullable', 'regex:/^[a-zA-Z0-9!#$%&()*+,.:;=?@\[\]^_{}-]+$/', 'max:64'],
            $this->headerStr['yesterday_flag'] => ['nullable', 'in:0,1'],
            $this->headerStr['today_flag'] => ['nullable', 'in:0,1'],
            $this->headerStr['address'] => ['nullable', 'string', 'max:256'],
            $this->headerStr['latitude'] => ['nullable', /*"required_with:{$this->headerStr['address']}", */'numeric'],
            $this->headerStr['longitude'] => ['nullable', /*"required_with:{$this->headerStr['address']}", */'numeric'],
            $this->headerStr['field_id'] => ['nullable', "required_with:{$this->headerStr['address']}", 'exists:t_field,name', $in ? Rule::in([$in]) : ""],
            $this->headerStr['required_time'] => ['nullable', 'integer', 'min:0', 'max:2400'],
            $this->headerStr['email_time'] => ['nullable', 'integer', 'min:0', 'max:2400'],

            '*.' . $this->headerStr['code'] => ['required'],
            '*.' . $this->headerStr['email'] => ['required', 'email', 'unique:t_staff,email'],
            '*.' . $this->headerStr['name'] => ['required', 'string', 'max:64', 'regex:/^[ぁ-んァ-ヶ一-龥々０-９ａ-ｚＡ-Ｚ 　]+$/u'],
            '*.' . $this->headerStr['furigana'] => ['nullable', 'string', 'max:64', 'regex:/^[あ-ん゛゜ぁ-ぉゃ-ょー「」、 　]+$/u'],
            '*.' . $this->headerStr['tel']=> ['nullable', 'regex:/^[0-9]+$/', 'max:11'],
            '*.' . $this->headerStr['staff_role_id'] => ['nullable', 'exists:t_staff_roles,name'],
            '*.' . $this->headerStr['password'] => ['nullable', 'regex:/^[a-zA-Z0-9!#$%&()*+,.:;=?@\[\]^_{}-]+$/', 'max:64'],
            '*.' . $this->headerStr['yesterday_flag'] => ['nullable', 'in:0,1'],
            '*.' . $this->headerStr['today_flag'] => ['nullable', 'in:0,1'],
            '*.' . $this->headerStr['address'] => ['nullable', 'string', 'max:256'],
            '*.' . $this->headerStr['latitude'] => ['nullable', /*"required_with:*.{$this->headerStr['address']}", */'numeric'],
            '*.' . $this->headerStr['longitude'] => ['nullable', /*"required_with:*.{$this->headerStr['address']}", */'numeric'],
            '*.' . $this->headerStr['field_id'] => ['nullable', "required_with:*.{$this->headerStr['address']}", 'exists:t_field,name'],
            '*.' . $this->headerStr['required_time'] => ['nullable', 'integer', 'min:0', 'max:2400'],
            '*.' . $this->headerStr['email_time'] => ['nullable', 'integer', 'min:0', 'max:2400'],
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'input_encoding' => 'SJIS'
        ];
    }
}