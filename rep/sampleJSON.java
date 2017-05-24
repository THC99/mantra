/**
 * Contoh pemanfaatan WebAPI dalam pemrograman Java 
 * Programmed by: Didi Sukyadi, Agung Basuki, Diansyah.
 * ver:1.99y
 **/

import java.io.*;
import java.net.*;

public class sampleJSON {

	public static void main(String[] args) throws Exception{
	String endPoint="%URL%";
	String methodName="%METHOD%";
	String accessKey="%ACCESSKEY%";
	String reqInput="%INPUT%";
	String param="";
		if(!reqInput.equals("")){
			if(args.length>0){
				if(reqInput.indexOf(",")>-1){
					Integer i=0;
					for(String parname:reqInput.split(",")){
						if(args.length>=i) param+=(param.equals("")?parname+"="+URLEncoder.encode(args[i],"UTF-8"):"&"+parname+"="+URLEncoder.encode(args[i],"UTF-8"));        				 			
						i++;
					}
				}
				else{
					param=reqInput+"="+URLEncoder.encode(args[0],"UTF-8");					
				}
			}
			else{
				System.out.println("Empty Parameter");
				System.exit(0);
			}
		}
			
		if(endPoint.equals("")){
			System.out.println("Empty Web-API URL/Endpoint.");
			System.exit(0);
		}

		try{
			URL urlAPI= new URL(endPoint+"/"+methodName+"/"+param);		
			HttpURLConnection objAPI = (HttpURLConnection) urlAPI.openConnection();
			objAPI.addRequestProperty("User-Agent", "MANTRA");
			objAPI.addRequestProperty("AccessKey", "%ACCESSKEY%");
		
			if (objAPI.getResponseCode() != 200) {
				throw new Exception(objAPI.getResponseMessage());
			}
		
			String result=getStringInputStream(objAPI.getInputStream());
			objAPI.disconnect();
			System.out.println("result = " + result);
		}
		catch(IOException e){
			System.out.println(e);
		}

	}
    
  public static String getStringInputStream(InputStream instream) throws IOException {
		if(instream instanceof InputStream){
			BufferedReader buffStream = new BufferedReader(new InputStreamReader(instream));
			StringBuilder textData = new StringBuilder();
			String textLine;
			while ((textLine = buffStream.readLine()) != null ){
				textData.append(textLine);
			}
			buffStream.close();
			return textData.toString();
		}
		else return "";
  }
}

