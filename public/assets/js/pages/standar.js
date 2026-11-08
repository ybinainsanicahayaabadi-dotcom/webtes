(function ($) {
 'use strict';
 $('.standar-table').each(function () {
  SISLIA.table(this, {order:[[1,'asc']], columnDefs:[{orderable:false,targets:[4,5]}]});
 });
 $(document).on('click','[data-editor]',function () {
  var kind=$(this).attr('data-editor');
  var modal=document.getElementById('editor-'+kind);
  if(!modal)return;
  var form=modal.querySelector('form'); form.reset();
  form.querySelector('[name="id"]').value='0';
  var record=JSON.parse($(this).attr('data-record')||'{}');
  // Iterasi field terdaftar: nama dari JSON tidak dijadikan selector HTML.
  Array.prototype.forEach.call(form.elements,function (f) {
   if(!f.name || !Object.prototype.hasOwnProperty.call(record,f.name) || f.type==='file')return;
   if(f.type==='checkbox') f.checked=Number(record[f.name])===1;
   else f.value=record[f.name]===null?'':record[f.name];
  });
  bootstrap.Modal.getOrCreateInstance(modal).show();
 });
})(jQuery);
