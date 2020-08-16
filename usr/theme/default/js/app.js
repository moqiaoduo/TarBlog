// 初版用了jquery，现在完全使用原生js，去除对jquery的依赖
(function () {
    var hasFind = false;
    var menus = document.querySelectorAll('.menu a'); // 使用了querySelector进行选择器筛选，最低支持IE8
    var cd = document.querySelector('.category-dropdown');
    var cl = document.querySelector('.dropdown-list');
    var elem;

    // 寻找当前页面对应的标题
    for (var i=0; i<menus.length; i++) {
        elem = menus[i];
        if (elem.href === window.location.href) {
            elem.classList.add('current')
            hasFind = true;
            break;
        }
    }

    if (!hasFind) {
        var last_mod;
        for (i=0; i<menus.length; i++) {
            elem = menus[i];
            if (window.location.href.indexOf(this.href) !== -1) {
                if (last_mod) last_mod.classList.remove('current')
                elem.addClass('current');
                last_mod = elem;
            }
        }
    }

    // 加载时和窗口大小变化时触发重新定位
    window.onload = ps
    window.onresize = ps

    function ps() { // 分类菜单定位
        var elemRect = cd.getBoundingClientRect();
        var left = elemRect.left - cl.offsetWidth;
        cl.style.left = left + 'px';

        cl.style.top = (elemRect.top + 33) + 'px'
    }
})();