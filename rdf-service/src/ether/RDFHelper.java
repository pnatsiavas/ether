/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package ether;

import com.hp.hpl.jena.datatypes.xsd.XSDDatatype;
import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.ModelFactory;
import com.hp.hpl.jena.rdf.model.Property;
import com.hp.hpl.jena.rdf.model.Resource;
import com.hp.hpl.jena.rdf.model.ResourceFactory;
import com.hp.hpl.jena.vocabulary.RDF;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.GregorianCalendar;
import java.util.HashMap;
import org.json.JSONObject;

/**
 *
 * @author beredim
 */

public class RDFHelper {
    
    static HashMap<String, String> pollutantHourlyMap = createMap_pollutantHourly();
    static HashMap<String, String> pollutantDailyMap = createMap_pollutantDaily();
    static HashMap<String, String> stationMap = createMap_station();
    static HashMap<String, String> levelMap = createMap_level();
    
    private static HashMap<String,String> createMap_pollutantHourly(){
        HashMap<String,String> map = new HashMap<String, String>();
        map.put("so2", "SulphurDioxide");
        map.put("pm10", "PM10");
        map.put("co", "CarbonMonoxide");
        map.put("no2", "NitrogenDioxide");
        map.put("o3", "Ozone");
        return map;
    }
    
    private static HashMap<String,String> createMap_pollutantDaily(){
        HashMap<String,String> map = new HashMap<String, String>();
        map.put("ypm10", "PM10");
        return map;
    }
    
    private static HashMap<String,String> createMap_level(){
        HashMap<String,String> map = new HashMap<String, String>();
        map.put("χαμηλά", "Low");
        map.put("μέτρια", "Medium");
        map.put("αυξημένα", "High");
        map.put("υψηλά", "VeryHigh");
        map.put("πολύ υψηλά", "Severe");
        return map;
    }
    
    private static HashMap<String,String> createMap_station(){
        HashMap<String,String> map = new HashMap<String, String>();
        map.put("1", "Egnatias");
        map.put("2", "Martiou");
        map.put("3", "Lagkada");
        map.put("4", "Eptapyrgiou");
        map.put("5", "Toumpas");
        map.put("6", "CityHall");
        return map;
    }
    
    public static Model create(String jsonInput, String date){
        JSONObject jso = new JSONObject(jsonInput);
        String today="";
        String yesterday="";
        String etherSchemaFileName = "ether-schema.ttl";
        Model model = ModelFactory.createDefaultModel();
        model.read(etherSchemaFileName, "TURTLE") ;
        
        try{
            SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd");
            Calendar temp = new GregorianCalendar();
            temp.setTime(sdf.parse(date));
            temp.add(Calendar.DAY_OF_MONTH, -1);
            yesterday = sdf.format(temp.getTime());
            today = date;
        }catch(Exception e){
            e.printStackTrace();
        }
        
        String qualityToday = jso.getString("gentoday").trim();
        String qualityYesterday = jso.getString("genyest").trim();
        
        if(levelMap.containsKey(qualityToday)){
            
            Resource todayEstimate = model.createResource();
            Resource rdfType = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#AirPollutionQualityEstimate");
            todayEstimate.addProperty(RDF.type, rdfType);
            
            Property estimateForNetwork = ResourceFactory.createProperty("http://med.auth.gr/lomi/ether-schema#estimateForNetwork");
            Resource network = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#ThessalonikiMunicipalAirPollutionMeasurementNetwork");
            todayEstimate.addProperty(estimateForNetwork,network);
            
            Property measurementDate = ResourceFactory.createProperty("http://med.auth.gr/lomi/ether-schema#measurementDate");
            model.add(todayEstimate,measurementDate,ResourceFactory.createTypedLiteral(today, XSDDatatype.XSDdate));
            
            Property qualityEstimateValue = ResourceFactory.createProperty("http://med.auth.gr/lomi/ether-schema#qualityEstimateValue");
            Resource value = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#"+levelMap.get(qualityToday));
            todayEstimate.addProperty(qualityEstimateValue,value);
        }
        
        if(levelMap.containsKey(qualityYesterday)){
            
            Resource yesterdayEstimate = model.createResource();
            Resource rdfType = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#AirPollutionQualityEstimate");
            yesterdayEstimate.addProperty(RDF.type, rdfType);
            
            Property estimateForNetwork = ResourceFactory.createProperty("http://med.auth.gr/lomi/ether-schema#estimateForNetwork");
            Resource network = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#ThessalonikiMunicipalAirPollutionMeasurementNetwork");
            yesterdayEstimate.addProperty(estimateForNetwork,network);
            
            Property measurementDate = ResourceFactory.createProperty("http://med.auth.gr/lomi/ether-schema#measurementDate");
            model.add(yesterdayEstimate,measurementDate,ResourceFactory.createTypedLiteral(yesterday, XSDDatatype.XSDdate));
            
            Property qualityEstimateValue = ResourceFactory.createProperty("http://med.auth.gr/lomi/ether-schema#qualityEstimateValue");
            Resource value = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#"+levelMap.get(qualityYesterday));
            yesterdayEstimate.addProperty(qualityEstimateValue,value);
        }
        
        for (String keyP : pollutantDailyMap.keySet()) {
            for (String keyS : stationMap.keySet()){
                String jsonKey = keyP+"_"+keyS;
                try{
                    Double value = jso.getDouble(jsonKey);
                    Resource dailyMeasurement = model.createResource();
                    
                    Resource rdfType = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#AverageDailyMeasurement");                    
                    dailyMeasurement.addProperty(RDF.type, rdfType);
                    
                    Property measurementDate = ResourceFactory.createProperty("http://med.auth.gr/lomi/ether-schema#measurementDate");
                    model.add(dailyMeasurement,measurementDate,ResourceFactory.createTypedLiteral(yesterday, XSDDatatype.XSDdate));
                    
                    Property measurementFromStation = ResourceFactory.createProperty("http://med.auth.gr/lomi/ether-schema#measurementFromStation");
                    Resource station = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#"+stationMap.get(keyS));
                    dailyMeasurement.addProperty(measurementFromStation,station);
                    
                    Property measurementOfPollutant = ResourceFactory.createProperty("http://med.auth.gr/lomi/ether-schema#measurementOfPollutant");
                    Resource pollutant = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#"+pollutantDailyMap.get(keyP));
                    dailyMeasurement.addProperty(measurementOfPollutant,pollutant);
                    
                    Property measurementValue = ResourceFactory.createProperty("http://med.auth.gr/lomi/ether-schema#measurementValue");
                    model.add(dailyMeasurement,measurementValue,ResourceFactory.createTypedLiteral(value.toString(),XSDDatatype.XSDfloat));
                    
                    Property quantity = ResourceFactory.createProperty("http://qudt.org/schema/qudt#quantity");
                    Resource density = ResourceFactory.createResource("http://qudt.org/vocab/quantity#Density");
                    model.add(dailyMeasurement,quantity,density);
                    
                    Property unit = ResourceFactory.createProperty("http://qudt.org/schema/qudt#unit");
                    Resource micro = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#MicrogramPerCubicMeter");
                    Resource milli = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#MilligramPerCubicMeter");
                    if(keyP.equals("co")){
                        model.add(dailyMeasurement,unit,milli);
                    }else{
                        model.add(dailyMeasurement,unit,micro);
                    }
                }catch(Exception e){
                    //nothing
                }
            }
        }
        for (String keyP : pollutantHourlyMap.keySet()) {
            for (String keyS : stationMap.keySet()){
                String jsonKey = keyP+"_"+keyS;
                try{
                    Double value = jso.getDouble(jsonKey);
                    Resource hourlyMeasurement = model.createResource();
                    
                    Resource rdfType = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#MaximumHourlyMeasurement");                    
                    hourlyMeasurement.addProperty(RDF.type, rdfType);
                    
                    Property measurementDate = ResourceFactory.createProperty("http://med.auth.gr/lomi/ether-schema#measurementDate");
                    model.add(hourlyMeasurement,measurementDate,ResourceFactory.createTypedLiteral(today, XSDDatatype.XSDdate));
                    
                    Property measurementFromStation = ResourceFactory.createProperty("http://med.auth.gr/lomi/ether-schema#measurementFromStation");
                    Resource station = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#"+stationMap.get(keyS));
                    hourlyMeasurement.addProperty(measurementFromStation,station);
                    
                    Property measurementOfPollutant = ResourceFactory.createProperty("http://med.auth.gr/lomi/ether-schema#measurementOfPollutant");
                    Resource pollutant = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#"+pollutantHourlyMap.get(keyP));
                    hourlyMeasurement.addProperty(measurementOfPollutant,pollutant);
                    
                    Property measurementValue = ResourceFactory.createProperty("http://med.auth.gr/lomi/ether-schema#measurementValue");
                    model.add(hourlyMeasurement,measurementValue,ResourceFactory.createTypedLiteral(value.toString(),XSDDatatype.XSDfloat));
                    
                    Property quantity = ResourceFactory.createProperty("http://qudt.org/schema/qudt#quantity");
                    Resource density = ResourceFactory.createResource("http://qudt.org/vocab/quantity#Density");
                    model.add(hourlyMeasurement,quantity,density);
                    
                    Property unit = ResourceFactory.createProperty("http://qudt.org/schema/qudt#unit");
                    Resource micro = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#MicrogramPerCubicMeter");
                    Resource milli = ResourceFactory.createResource("http://med.auth.gr/lomi/ether-schema#MilligramPerCubicMeter");
                    if(keyP.equals("co")){
                        model.add(hourlyMeasurement,unit,milli);
                    }else{
                        model.add(hourlyMeasurement,unit,micro);
                    }
                }catch(Exception e){
                    //nothing
                }
            }
        }
        
        return model;
    }
    
}
