<?php
/**
 * 树结构处理类
 */
namespace core\plugin;

class Tree
{

    public $icon = array('│', '├─', '└─');
    public $nbsp = "&nbsp;";

    //使用静态初始化字符串，否则递归无法进行正常的字符串拼接
    public static $select_tree = '';

    /*
     * 得到树结构的数组
     * */
    public function get_tree_array($data, $parent_id, $layer = 1)
    {
        //定义一个空的数组，用于存放处理好树结构数组
        $tree = array();
        foreach ($data as $key => $value) {
            //判断是不是父级
            if ($value['parent_id'] == $parent_id) {
                //写入层级
                $value['layer'] = $layer;
                //通过自身id递归方法寻找有没有子类，直到找不到子类为止
                $value['child'] = $this->get_tree_array($data, $value['menu_id'], $layer + 1);
                //写入数组
                $tree[] = $value;
            }
        }
        return array_reverse($tree);
    }

    /*
     * 得到基础树结构
     *
     * │安徽
     *    └─合肥
     *      └─合肥北
     * │北京
     *    └─海淀
     *      ├─中关村
     *      └─上地
     * │河北
     *    └─石家庄
     * */
    public function get_tree($arr)
    {
        //使用静态初始化字符串，否则递归无法进行正常的字符串拼接
        static $tree = '';
        //获取树结构最后一位key
        $n = count($arr) - 1;
        foreach ($arr as $k => $v) {
            //父样式
            $f_style = str_repeat($this->nbsp . $this->nbsp . $this->nbsp, $v['layer']) . $this->icon[0];
            //子样式
            $c_style = str_repeat($this->nbsp . $this->nbsp . $this->nbsp . $this->nbsp, $v['layer']) . $this->icon[1];
            //最后的样式
            $c_last_style = str_repeat($this->nbsp . $this->nbsp . $this->nbsp . $this->nbsp, $v['layer']) . $this->icon[2];
            if (!empty($v['child'])) {
                //处理结构样式
                if ($v['parent_id'] == 0) {
                    $style = $f_style;
                } else {
                    if ($k != $n) {
                        $style = $c_style;
                    } else {
                        $style = $c_last_style;
                    }
                }
                //拼接树结构
                $tree .= $style . $v['name'] . "<br/>";
                //此结构有子结构，继续递归调用，生成树结构
                $this->get_tree($v['child']);
            } else {
                //处理结构样式
                if ($v['parent_id'] == 0) {
                    $style = $f_style;
                } else {
                    if ($k != $n) {
                        $style = $c_style;
                    } else {
                        $style = $c_last_style;
                    }
                }
                $tree .= $style . $v['name'] . "<br/>";
            }
        }
        return $tree;
    }

    //
    public function get_select_tree($arr, $selected = false)
    {
        //使用静态初始化字符串，否则递归无法进行正常的字符串拼接
        static $tree = '';
        //获取树结构最后一位key
        $n = count($arr) - 1;
        foreach ($arr as $k => $v) {
            //父样式
            $f_style = str_repeat($this->nbsp . $this->nbsp . $this->nbsp, $v['layer']) . $this->icon[0];
            //子样式
            $c_style = str_repeat($this->nbsp . $this->nbsp . $this->nbsp . $this->nbsp, $v['layer']) . $this->icon[1];
            //最后的样式
            $c_last_style = str_repeat($this->nbsp . $this->nbsp . $this->nbsp . $this->nbsp, $v['layer']) . $this->icon[2];
            if (!empty($v['child'])) {
                //处理结构样式
                if ($v['parent_id'] == 0) {
                    $selecteds = $selected == $v['menu_id'] ? 'selected="selected"' : '';
                    //拼接树结构
                    $tree .= '<option ' . $selecteds . ' value="' . $v['menu_id'] . '">' . $f_style . $v['name'] . "</option>";
                } else {
                    if ($k != $n) {
                        $tree .= '<optgroup label="' . $c_style . $v['name'] . '"></optgroup>';
                    } else {
                        $tree .= '<optgroup label="' . $c_last_style . $v['name'] . '"></optgroup>';
                    }
                }
                //此结构有子结构，继续递归调用，生成树结构
                $this->get_select_tree($v['child']);
            } else {
                //处理结构样式
                if ($v['parent_id'] == 0) {
                    $selecteds = $selected == $v['menu_id'] ? 'selected="selected"' : '';
                    //拼接树结构
                    $tree .= '<option ' . $selecteds . ' value="' . $v['menu_id'] . '">' . $f_style . $v['name'] . "</option>";
                } else {
                    if ($k != $n) {
                        $tree .= '<optgroup label="' . $c_style . $v['name'] . '"></optgroup>';
                    } else {
                        $tree .= '<optgroup label="' . $c_last_style . $v['name'] . '"></optgroup>';
                    }
                }
            }
        }
        return $tree;
    }

    //获取普通select
    public function get_ordinary_select_tree($arr, $selected = false)
    {
        //获取树结构最后一位key
        $n = count($arr) - 1;
        foreach ($arr as $k => $v) {
            //父样式
            $f_style = str_repeat($this->nbsp . $this->nbsp . $this->nbsp, $v['layer']) . $this->icon[0];
            //子样式
            $c_style = str_repeat($this->nbsp . $this->nbsp . $this->nbsp . $this->nbsp, $v['layer']) . $this->icon[1];
            //最后的样式
            $c_last_style = str_repeat($this->nbsp . $this->nbsp . $this->nbsp . $this->nbsp, $v['layer']) . $this->icon[2];
            $selecteds = $selected ? 'selected="selected"' : '';
            if (!empty($v['child'])) {
                //处理结构样式
                if ($v['parent_id'] == 0) {
                    //拼接树结构
                    self::$select_tree .= '<option ' . $selecteds . ' value="' . $v['menu_id'] . ',' . $v['parent_id'] . '">' . $f_style . $v['name'] . "</option>";
                } else {
                    if ($k != $n) {
                        self::$select_tree .= '<option ' . $selecteds . ' value="' . $v['menu_id'] . ',' . $v['parent_id'] . '">' . $c_style . $v['name'] . '</option>';
                    } else {
                        self::$select_tree .= '<option ' . $selecteds . ' value="' . $v['menu_id'] . ',' . $v['parent_id'] . '">' . $c_last_style . $v['name'] . '</option>';
                    }
                }
                //此结构有子结构，继续递归调用，生成树结构
                $this->get_ordinary_select_tree($v['child'], $selected);
            } else {
                //处理结构样式
                if ($v['parent_id'] == 0) {
                    //拼接树结构
                    self::$select_tree .= '<option ' . $selecteds . ' value="' . $v['menu_id'] . ',' . $v['parent_id'] . '">' . $f_style . $v['name'] . "</option>";
                } else {
                    if ($k != $n) {
                        self::$select_tree .= '<option ' . $selecteds . ' value="' . $v['menu_id'] . ',' . $v['parent_id'] . '">' . $c_style . $v['name'] . '</option>';
                    } else {
                        self::$select_tree .= '<option ' . $selecteds . ' value="' . $v['menu_id'] . ',' . $v['parent_id'] . '">' . $c_last_style . $v['name'] . '</option>';
                    }
                }
            }
        }
        return self::$select_tree;
    }

    //获得后台左侧树形结构菜单 ul li
    public function get_backstage_menu($tree, $url, $i = true, $y = false, $z = 0)
    {
        $html = '';
        foreach ($tree as $key => $value) {
            if ($y && $value['parent_id'] == 0) {
                $px = 39 * $z;
                $style = 'style="transform: translateY(' . $px . 'px);"';
            } else {
                $style = '';
            }
            //判断菜单是否是被选中的状态
            $active = '';
            if ($_SESSION['routing']['module'] == $value['module']) {
                if ($_SESSION['routing']['controller'] == $value['controller']) {
                    $active = 'active';
                }
            }
            //判断子数组，是否存在子
            if (count($value['child']) <= 0 && $value['parent_id'] == 0) {
                $module = $value['module'] != '' ? $value['module'] . '/' : '';
                $controller = $value['controller'] != '' ? $value['controller'] . '/' : '';
                $html .= '<li class="' . $active . '" ' . $style . '><a href="' . $url . '/cool/' . $module . $controller . $value['methods'] . '" class="' . $value['icon'] . '">' . $value['name'] . '</a></li>';
            } elseif (count($value['child']) <= 0) {
                $module = $value['module'] != '' ? $value['module'] . '/' : '';
                $controller = $value['controller'] != '' ? $value['controller'] . '/' : '';
                $html .= '<li class="' . $active . '" ' . $style . '><a href="' . $url . '/cool/' . $module . $controller . $value['methods'] . '">' . $value['name'] . '</a></li>';
            } else {
                $open = '';
                //判断后台左侧菜单是否展开
                $cm_menu_toggle = \core\plugin\Cookie::get('cm-menu-toggled', false);
                if ($cm_menu_toggle == 'false' || $cm_menu_toggle == '') {
                    //判断是否有子菜单是选中的
                    foreach ($value['child'] as $k => $v) {
                        if ($_SESSION['routing']['module'] == $v['module']) {
                            if ($_SESSION['routing']['controller'] == $v['controller']) {
                                $open = ' open';
                                $y = true;
                                $z = count($value['child']);
                            }
                        }
                    }

                }
                $html .= '<li class="cm-submenu' . $open . '" ' . $style . '><a class="' . $value['icon'] . '">' . $value['name'] . ' <span class="caret"></span></a>';
                $html .= $this->get_backstage_menu($value['child'], $url, false, $y, $z);
                $html = $html . '</li>';
            }
        }
        if ($i) {
            return $html ? '<ul class="cm-menu-items">' . $html . '</ul>' : $html;
        } else {
            return $html ? '<ul>' . $html . '</ul>' : $html;
        }
    }

}