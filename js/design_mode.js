function designMode()
{
    with(document.getElementById("f").contentWindow.document){
        open();
        write((document.getElementById("text").value));
        close();
        designMode="on";
    }
    document.getElementById("f").contentWindow.focus();
}
