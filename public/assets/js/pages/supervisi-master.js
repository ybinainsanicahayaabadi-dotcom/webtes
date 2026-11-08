(function($){
 'use strict';
 $('.master-table').each(function(){SISLIA.table(this,{order:[],columnDefs:[{targets:3,orderable:false}]});});
 $(document).on('click','[data-master]',function(){
  var m=document.getElementById('master-'+this.getAttribute('data-master'));if(!m)return;
  var f=m.querySelector('form');f.reset();f.querySelector('[name="id"]').value='0';
  var d=JSON.parse(this.getAttribute('data-record')||'{}');
  Array.prototype.forEach.call(f.elements,function(x){
   // Hanya field data, jangan menimpa kind/instrumen_id/token dari record.
   if(['kind','instrumen_id','_token'].indexOf(x.name)>=0)return;
   if(!Object.prototype.hasOwnProperty.call(d,x.name))return;
   if(x.type==='checkbox')x.checked=Number(d[x.name])===1;
   else x.value=d[x.name]===null?'':d[x.name];
  });bootstrap.Modal.getOrCreateInstance(m).show();
 });
})(jQuery);
