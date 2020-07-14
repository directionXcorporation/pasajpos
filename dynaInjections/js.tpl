try{
    app.service('{{_NAME}}', {{_JAVASCRIPT}});
    console.log("Finished setting up injection: {{_NAME}}");
}catch(e){
    console.error(["Error in setting up injection: {{_NAME}}", e]);
}