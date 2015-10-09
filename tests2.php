<?php




/*
 * 
 *  1) конфиг приложения сохраним в базе данных исходя из того, что баннеров может быть значительное количество
 *  2) метод get в любом случае отнимает количество показанных баннеров от значения в базе 
 *  3) получаем некое подобие ActiveRecord
 */


class Banner {
    protected $db;
    protected $banner_name;
    protected $codes_count = 50;
    private $extensions = array (
        'php', 'html', 'htm', 'tpl'
    );
    
    public function __construct($name) {
        
        $full_name = $this->checkFile($name);
        if (!$full_name)
            throw new Exception ("Не найден файл баннера");
        $this->banner_name = $full_name;
        $this->getConnect();
        $this->getBannerFromBase();
    }
    
    private function checkFile ($name) {
        foreach ($this->extensions as $extension) {
            if (file_exists($name.'.'.$extension))
                return $name.'.'.$extension;
        }
        return false;
    }
    
    //=====================================================
    /*
     * getConnect - осуществляет соединение с базой
     */
    private function getConnect () {
        $this->db = new PDO('mysql:host=localhost;dbname=dima_test','***','***');
    }
    
    /*
     * getBannerFromBase - получает строку из базы по имени, либо создает новую при отсутствии
     */
    private function getBannerFromBase() {
        $query = $this->db->query("SELECT * FROM banners WHERE name='".$this->banner_name."'");
        $row = $query->fetch();
        if ($row != NULL) {
            $this->codes_count = $row['count'];
        }
        else {
            $this->db->query("INSERT INTO banners VALUES (null, '".$this->banner_name."','".$this->codes_count."')");
        }
    }
    
    /*
     * setToBase - устанавливает новое значение count в базе в соответствии с текущим значением this->codes_count
     */
    private function setToBase () {
        $this->db->query("UPDATE banners "
                . "SET count='" . $this->codes_count . "' "
                . "WHERE name='".$this->banner_name."'");
    }
    //=====================================================
    
    
    /*
     * get - возвращает count кодов из файла banner_name
     * $count - количество кодов либо процент от заданного количества кадов 
     * $banner->get('50%');
     * $banner->get(20);
     */
    public function get ($count = NULL) {
        if ($this->codes_count < $count)
            return false;
        $content = file_get_contents($this->banner_name);
        
        if (is_int($count)) {
            $count = $count;
        }
        else if ($count == NULL) {
            $count = $this->codes_count;
        }
        else {
            $count = substr($count, 0, -1);
            $count = $this->codes_count * 40 / 100;
        }
        
        for ($i = 0; $i < $count; $i++)
            $result .= $content;
        $this->codes_count -= $count;
        $this->setToBase();
        return $result;
    }
    
    
    public function setConfig ($count) {
        $this->codes_count = $count;
        $this->setToBase();
    }
    
}






$banner = new Banner ('banner');
echo $banner->get(50);

$banner_id = new Banner ('ban');
echo $banner_id->get(1);
//задать значение по умолчанию
$banner->setConfig(100);

?>


<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>

    var id = window.location.hash.replace("#","");
    banner_id = id.split('-')[1];
    var offset = $("#"+banner_id).offset().top ;
    $("body").animate({ scrollTop: offset}, 1000 );
    $("#"+banner_id).css({
        'border': '1px solid rgb(255, 0, 0)',
        'display' : 'inline-block'
    });
</script>
