const router=require('express').Router()
const {login,urunekle,data}=require('../controllers/controller')
router.post("/login",login)
router.get("/data",datatable)
router.put("/urunekle",urunekle)

module.exports=router
