const dbConn=require("database.js")
const bcrypt=require("bcrypt")
const Response=require("../utils/response")
const login=async(req,res)=>{
    const kullanici_ad=req.body.kullanici_ad
    const sifre=req.body.sifre
    dbConn.query("SELECT * FROM kullanici WHERE kullanici_ad=?",
    [kullanici_ad],(error,results)=>{
        if(results.length>0){
            const comparePassword=bcrypt.compare(sifre,results[0].sifre)
            if(comparePassword){
               return new Response(results).basarili_giris(res)
            }else{
                return res.status(203).json({
                    success:false,
                    message:"Kullanıcı veya Şifre Uyumsuz"
                })
            }
        }else{
            return res.status(203).json({
                success:false,
                message:"Kullanıcı Girişi Başarısız"
            })
        }
    })
}




const data=(req,res)=>{
    dbConn.query("SELECT* FROM urun",(error,result)=>{
        if(error){
            console.log("Sunucu yanıt vermiyor")
            return new Response().error500(res)
        }else{
            res.json({data:result})
        }
    })
}


module.exports={login,data}