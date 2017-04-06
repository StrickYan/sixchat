
function formatImg(imgObject){
    if(imgObject.height > 130 || imgObject.width > 200){
        var hw = imgObject.height/imgObject.width;
        var hh = imgObject.height/130;
        var ww = imgObject.width/200;
        if (hh>ww) {
            imgObject.height = 130;
            imgObject.width = 130/hw;
        } else {
            imgObject.height = 200*hw;
            imgObject.width = 200;
        }
    }
    
    //imgObject.height = 130;
    //imgObject.width = 200;
}

function formatImg_2(imgObject){
    // if(imgObject.height > 1300 || imgObject.width > 2000){
    //     var hw = imgObject.height/imgObject.width;
    //     var hh = imgObject.height/1300;
    //     var ww = imgObject.width/2000;
    //     if (hh>ww) {
    //         imgObject.height = 1300;
    //         imgObject.width = 1300/hw;
    //     } else {
    //         imgObject.height = 2000*hw;
    //         imgObject.width = 2000;
    //     }
    // }
}