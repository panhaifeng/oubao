<?php
function theme_widget_qf_navs(&$setting, &$system) {
    if ($setting['menus']) {
        foreach ($setting['menus'] as $menus) {
            $data[] = $menus;
        }
    }

    return $data;
}
?>
