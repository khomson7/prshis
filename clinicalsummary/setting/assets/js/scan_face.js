function showPreviewOne(event){
    if(event.target.files.length > 0){
      let src = URL.createObjectURL(event.target.files[0]);
      let preview = document.getElementById("file-ip-1-preview");
      preview.src = src;
      preview.style.display = "block";
    } 
  }
  function myImgRemoveFunctionOne() {
    document.getElementById("file-ip-1-preview").src = "https://i.ibb.co/ZVFsg37/default.png";
  }