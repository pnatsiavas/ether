/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package ether;

import com.hp.hpl.jena.rdf.model.Model;
import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.StringWriter;
import java.net.HttpURLConnection;
import java.net.URL;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import javax.ws.rs.Produces;
import javax.ws.rs.WebApplicationException;

/**
 *
 * @author beredim
 */


@Path("/ether-data")
public class API {

    
    @GET
    @Path("/{date}")
    @Produces("application/rdf+xml")
    public String getData(@PathParam("date") String date){
        
        try{
        SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd");
        Date result = sdf.parse(date);
        }catch(ParseException e){
            throw new WebApplicationException(400);
        }
        
        String jsonRequest = "http://develop1.med.auth.gr:8080/envapp/json?date="+date;
        String jsonResponse="";
        try{
            URL url = new URL(jsonRequest);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            BufferedReader br = new BufferedReader(new InputStreamReader(conn.getInputStream()));
            String inputLine;
            
            while ((inputLine = br.readLine()) != null) {
                jsonResponse = jsonResponse + inputLine;
            }
            br.close();
            conn.disconnect();
        }catch(Exception e){
            throw new WebApplicationException(500);
        }
            if(jsonResponse.equals("null")){
            throw new WebApplicationException(404);
        }else{
                Model model = RDFHelper.create(jsonResponse, date);
                StringWriter out = new StringWriter();
                model.write(out);
                return out.toString();
        }
    }
}