// $(function () {
//     //select2 div下拉样式
//     function formatTopic (topic) {
//         return "<div class='select2-result-repository clearfix'>" +
//         "<div class='select2-result-repository__meta'>" +
//         "<div class='select2-result-repository__title'>" +
//         topic.name ? topic.name : ""   +
//             "</div></div></div>"
//     }
//
//     //领用人单位 二级联动
//
//         $(".receive_id").change(function () {
//
//         let id = $(".receive_id").val();
//         $.ajax({
//             url:"/api/equipment/employer",
//             dataType: "JSON",
//             data: {'id': id},
//             type: "GET",
//             success:function (data) {
//                 var gradeNum= data.length;
//                 var option = "<option value=''>请选择人员</option>";
//                 if(gradeNum>0){
//                     for(var i = 0;i<gradeNum;i++){
//                         option += "<option value='"+data[i].name+"'>"+data[i].name+"</option>";
//                     }
//                 }
//                 $("#employer").html(option);
//                 $("#dealer").html(option);
//                 $("#employer").val(name); //编辑时绑定
//                 $("#dealer").val(name); //编辑时绑定
//                 $("#employer").select2({ minimumResultsForSearch: -1 });//加载样式
//                 $("#dealer").select2({ minimumResultsForSearch: -1 });//加载样式
//             },
//             error:function(e) {
//                 layer.alert("系统异常，请稍候重试！");
//             }
//         })
//     })
//
// })