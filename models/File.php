<?php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class File extends Model
{
    /**
     * @var UploadedFile $file
     */
    public $file;

    public function rules()
    {
        return [
            [['file'], 'file'],
        ];
    }

    public function upload()
    {
        if (!empty($this->file)) {

            if (!move_uploaded_file($_FILES['news']['tmp_name']['image'], 'C:/wamp/www/mysite/images/' . $_FILES['news']['name']['image'])) {
                $res['msg'][] = 'Файл не удалось сохранить';
            }
            return true;
        } else {
            return false;
        }
    }
}
