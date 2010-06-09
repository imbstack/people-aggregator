function isValidManageEmbleum(form_name) {
  var form_obj, n, i;
  form_obj = document.forms[form_name];
  n = form_obj.elements.length;//total number of elements in the form
  for (i=0; i < n; i++) {  
    var test_string=new String()
    test_string='';
    test_string=''+form_obj.elements[i].name;
    test_length = test_string.length;
    temp=test_string.substring(0,test_length-1);
    switch (temp){
      case "userfile_url_": 
      strURL=form_obj.elements[i].value;
      if (strURL==""){
        alert("Please enter the destination URL");
        form_obj.elements[i].focus();
        return false;
       }
      var j = new RegExp(); 
      j.compile("^http://[A-Za-z0-9-]+\.[A-Za-z0-9]+");
      if (!j.test(strURL)) {
      alert("You must supply a valid URL.");
      form_obj.elements[i].focus();
      return false;
      }
      break;
    case "userfile_": 
      common="gif jpg jpeg png xpm bmp";
      strFile=''+form_obj.elements[i].value;
      ext_index=strFile.indexOf('.');
      len=strFile.length;
      sub_str=strFile.substring(ext_index+1,len);
      common_index=common.indexOf(sub_str.lower());
      if (common_index==-1){
        alert("Please select one of the following image types: gif jpg jpeg png xpm bmp");
        form_obj.elements[i].focus();
        return false
        }
      break;
   } // end of switch breaces
 }  // end of for loop
}  // function ending braces

