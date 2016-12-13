$(function () {

});

var global = {};
global.onload = [];
window.onload = function () {
    for (var i in global.onload) {
        global.onload[i]();
    }
};

var run_ascii = function (ribbon, framerate, easy) {
    framerate = easy ? framerate / 6 : framerate || 240;
    easy = easy || false;
    var current_frame = 0;
    $(ribbon).find('.frame').first().css({
        display: 'inline-block',
        opacity: 0
    }).animate({
        opacity: 1
    }, 300);
    setTimeout(function () {
        setInterval(function () {
            $(ribbon).find('.frame').eq(current_frame).siblings().hide().end().css('display', 'inline-block');
            current_frame = easy ?
                current_frame < $(ribbon).find('.frame').length - 5 ? current_frame + 6 : 0 :
                current_frame == $(ribbon).find('.frame').length ? 0 : current_frame + 1;
        }, 1000 / framerate);
    }, 600);
    return true;
};