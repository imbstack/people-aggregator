var toJSON = function(obj) {
  if(obj == null) { return "null"; } 
  switch (typeof(obj)) {
    case 'object':
      var v=[];
      for(var attr in obj) {
        if(typeof obj[attr] != "function") {
          v.push('"' + attr + '": ' + toJSON(obj[attr]));
        }
      }
      return "{" + v.join(", ") + "}";
      break;
    case 'array':
      for(var i=0,json=[];i<obj.length;i++)
        json[i] = (obj[i] != null) ? toJSON(obj[i]) : "null";
      return "["+json.join(", ")+"]"
      break;
    case 'string':
      return '"' +
        obj.replace(/(\\|\")/g,"\\$1")
        .replace(/\n|\r|\t/g,
          function(){
            var a = arguments[0];
            return  (a == '\n') ? '\\n':
                (a == '\r') ? '\\r':
                (a == '\t') ? '\\t': "";
          }
        ) +
        '"';
      break;
    defaukt:
      return obj;
      break;
  }
}