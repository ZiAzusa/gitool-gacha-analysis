function download(info) {
    alert('正在唤起浏览器下载...');
    location.href = '?download=' + info;
}
function openBg() {
    var imgLayer = document.getElementById("imgLayer");
    var imgBoxl = document.getElementById("imgBoxl");
    imgLayer.style.display = "block";
    imgBoxl.style.display = "block";
    imgSg();
}
function closeBg() {
    var imgLayer = document.getElementById("imgLayer");
    var imgBoxl = document.getElementById("imgBoxl");
    imgLayer.style.display = "none";
    imgBoxl.style.display = "none";
}
function imgSg() {
    var img = document.getElementById("bigimg");
    var imgw = img.naturalWidth;
    var imgh = img.naturalHeight;
    var userw = document.body.clientWidth;
    var userh = document.body.clientHeight;
    if (imgw >= (userw * 0.8) && imgh <= (userh * 0.8)) {
        img.style.width = "80%";
        img.style.height = "auto";
    } else if (imgh >= (userh * 0.8)) {
        img.style.width = "auto";
        img.style.height = "80%";
    } else {
        img.style.width = "auto";
        img.style.height = "auto";
    }
}
