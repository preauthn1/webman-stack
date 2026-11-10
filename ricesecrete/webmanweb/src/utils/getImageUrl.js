// 扫描 src/assets/images 目录下的所有图片文件
const images = import.meta.glob("@/assets/images/**/*",{eager:true})

export function getImageUrl(filename) {
    return images["/src/assets/images/"+filename].default
}
