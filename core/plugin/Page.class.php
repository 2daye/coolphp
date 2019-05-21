<?php
/*
 * 分页类
 * 1.传limit；通过$_GET传页数;
 * 2.display方法
 * 3.处理页数的范围；通过__set、__get处理类的私有属性;
 * 4.pageList:首页末页;
 * 5.select方法;jump方法
 * 6.a.自定义config参数，setConfig可以改变config的值
 *   b.display，根据传递数组的元素显示分页项目
 * 7.重写URL;
 */
namespace core\plugin;

class Page
{
    //sql语句中的limit
    private $limit;
    //每页的数据条数
    private $listRows;
    //当前页
    private $page;
    //总记录数
    private $total;
    //总页数
    private $pageNum;

    private $num;
    private $url;
    private $config = array('prev' => "«", "next" => "»");

    public function __construct($_total, $_listRows, $_num = 3)
    {
        $this->num = $_num;
        //从外部传入总记录数
        $this->total = $_total;
        //每页的条数由外部传入;
        $this->listRows = $_listRows;
        //总页数
        $this->pageNum = ceil($this->total / $this->listRows);
        //处理页数范围
        $this->handlePage();
        //分页公式;
        $this->limit = " limit " . ($this->page - 1) * $this->listRows . "," . $this->listRows;
        $this->url = $this->reWrite();
    }

    public function __set($_value, $_key)
    {
        $this->$_key = $_value;
    }

    public function __get($_key)
    {
        return $this->$_key;
    }

    private function reWrite()
    {
        $newURL = null;
        //获取path/queryString:/php3/oop/getData.php?action=show&id=8
        $url = $_SERVER['REQUEST_URI'];
        //echo "<hr>".$url."<hr>";
        //parse_url:解析路径，返回路径的组件，有query下标的是查询字符串;
        /*array(2) {
                ["path"]=>
                  string(21) "/php3/oop/getData.php"
                  ["query"]=>
                  string(16) "action=show&id=8"
                }
        */
        $parseURL = parse_url($url);
        //echo "<pre>";
        //var_dump($parseURL);
        //echo "</pre>";
        if (isset($parseURL['query'])) {
            //把查询字符解析到数组中;
            /**
             * array(2) {
             * ["action"]=>
             * string(4) "show"
             * ["page"]=>
             * string(1) "2"
             * }
             **/
            parse_str($parseURL['query'], $query);
            //echo "<pre>";
            //var_dump($query);
            //echo "</pre>";
            //销毁page元素
            unset($query['page']);
            //http_build_query:重新生成查询字符串;
            $newURL = $parseURL['path'] . "?" . http_build_query($query);
            //echo "<hr>".$newURL."<hr>";
        } else {
            $newURL = $parseURL['path'] . "?";
        }
        return $newURL;
    }

    /**
     * 重新设置config的值;
     *
     * @param string $_key
     * @param string $_value
     */
    public function setConfig($_key, $_value)
    {
        if (isset($this->config[$_key])) {
            $this->config[$_key] = $_value;
        } else {
            echo "<span class='text-danger'>config下标不存在</span>";
        }
    }

    /**
     * 处理页数范围
     *
     * 页数小于1时等于1；页数大于最大值时等于最大值；
     *   */
    private function handlePage()
    {
        //$_GET['page']默认值为1;
        $this->page = !empty($_GET['page']) ? $_GET['page'] : 1;
        if ($this->page > $this->pageNum) {
            $this->page = $this->pageNum;
        }
        if ($this->page < 1) {
            $this->page = 1;
        }
    }

    public function listRowsBegin()
    {
        return ($this->page - 1) * $this->listRows + 1;
    }

    /**
     * 首页
     * @return string
     */
    private function first()
    {
        $str = null;
        if ($this->page == 1) {
            //$str="<li class='disabled'><span>".$this->config['first']."</span></li>";
            $str = null;
        } else if ($this->page > $this->num + 2) {
            $str = "<li><a href='" . $this->url . "&page=1'>1</a></li><li><a>...</a></li>";
        } elseif ($this->page > $this->num + 1) {
            $str = "<li><a href='" . $this->url . "&page=1'>1</a></li>";
        }
        return $str;
    }

    /**
     * 前一页
     * @return string
     */
    private function prev()
    {
        $str = null;
        if ($this->page == 1) {
            $str = null;
        } else {
            $str = "<li><a href='" . $this->url . "&page=" . ($this->page - 1) . "'>" . $this->config['prev'] . "</a></li>";
        }
        return $str;
    }

    private function pageList()
    {
        $prev = null;
        $next = null;
        //当前页减
        for ($i = $this->num; $i >= 1; $i--) {
            if ($this->page - $i < 1) {
                continue;
            } else {
                $prev .= "<li><a href='" . $this->url . "&page=" . ($this->page - $i) . "'>" . ($this->page - $i) . "</a></li>";
            }
        }
        //当前页;
        $present = "<li class='active'><a>" . $this->page . "</a></li>";
        //当前页加
        for ($j = 1; $j <= $this->num; $j++) {
            if ($this->page + $j <= $this->pageNum) {
                $next .= "<li><a href='" . $this->url . "&page=" . ($this->page + $j) . "'>" . ($this->page + $j) . "</a></li>";
            } else {
                break;
            }
        }
        return $prev . $present . $next;
    }

    /**
     * 当前页
     * @return string
     */
    private function present()
    {
        return "<li class='active'><a>" . $this->page . "</span></a></li>";
    }

    /**
     * 下一页:当前页+1
     * @return string
     */
    private function next()
    {
        $str = null;
        if ($this->page == $this->pageNum) {
            $str = null;
        } else {
            $str = "<li><a href='" . $this->url . "&page=" . ($this->page + 1) . "'>" . $this->config['next'] . "</a></li>";
        }
        return $str;
    }

    /**
     * 显示末页
     *
     * 如果到了最后一页，末页不可以点击;不到最后一页，末页可以点击
     *
     * @return string $str：返回字符串
     */
    private function end()
    {
        $str = null;
        if ($this->page == $this->pageNum) {
            //$str.="<li class='disabled'><span>末页</span></li>";
            $str = null;
        } elseif ($this->pageNum - $this->page > $this->num + 1) {
            $str = "<li><a>...</a></li>
<li><a href='" . $this->url . "&page=" . ($this->pageNum) . "'>" . ($this->pageNum) . "</a></li>";
        } elseif ($this->pageNum - $this->page > $this->num) {
            $str = "<li><a href='" . $this->url . "&page=" . ($this->pageNum) . "'>" . ($this->pageNum) . "</a></li>";
        }
        return $str;
    }

    /**
     * 页数的select跳转
     * @return string
     */
    private function select()
    {
        $str = null;
        $str .= "<select class='form-control text-center' id='mySelect'>";
        for ($i = $this->num * 2; $i >= 1; $i--) {
            if ($this->page - $i < 1) {
                continue;
            } else {
                $str .= "<option value='" . ($this->page - $i) . "'>" .
                    ($this->page - $i . "/" . $this->pageNum) . "</option>";
            }
        }
        for ($j = 0; $j <= $this->num * 2; $j++) {
            if ($this->page + $j <= $this->pageNum) {
                if ($this->page == ($this->page + $j)) {
                    $str .= "<option value='" . ($this->page + $j) . "' selected='selected'>"
                        . ($this->page + $j . "/" . $this->pageNum) . "</option>";
                } else {
                    $str .= "<option value='" . ($this->page + $j) . "'>" . ($this->page + $j . "/" .
                            $this->pageNum) . "</option>";
                }
            } else {
                break;
            }
        }
        $str .= "</select>";
        return $str;
    }

    private function jump()
    {
        $str = null;
        $str .= "<li>";
        $str .= "<div style='width: 100px;margin-left: 6px;' class='input-group pull-right'>";
        $str .= "<input id='inputpage' type='text' class='form-control' value='" . $this->page . "'>";
        $str .= "<span class='input-group-btn'>";
        $str .= "<button id='jumpbtn' class='btn btn-default' type='button'>Go</button>";
        $str .= "</span>";
        $str .= "</div>";
        $str .= "</li>";
        return $str;
    }

    /**
     * 分页元素根据传递的数组的参数显示，默认全部显示.
     * 0：前一页，1：首页，2：循环数，3：末页，4：下一页，5：select，6：jump
     * @param array $_data
     * @return string
     */
    public function display($_data = array(0, 1, 2, 3, 4))
    {
        //参数必须是数组并且不能为空
        if (is_array($_data) && count($_data) != 0) {
            $str = "";
            $ui[0] = $this->prev();
            $ui[1] = $this->first();
            $ui[2] = $this->pageList();
            $ui[3] = $this->end();
            $ui[4] = $this->next();
            $ui[5] = $this->select();
            $ui[6] = $this->jump();
            $data = array(0, 1, 2, 3, 4, 5, 6);
            //var_dump($ui);
            foreach ($_data as $key => $value) {
                //下标不能超出范围并且元素只能是数字;
                if (in_array($value, $data) && is_int($value)) {
                    $str .= $ui[$value];
                } else {
                    echo "<span class='text-danger'>display方法参数的下标错误</span>";
                }
            }
        } else {
            echo "<span class='text-danger'>display方法传递的参数必须是非空数组</span>";
        }
        return $str;
    }
}