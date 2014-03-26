package gr.auth.med.lomi.ether.widget;

import java.text.DateFormat;
import java.text.DecimalFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Calendar;
import java.util.Date;
import java.util.Iterator;

import org.json.JSONException;
import org.json.JSONObject;

import android.app.PendingIntent;
import android.app.Service;
import android.appwidget.AppWidgetManager;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.Color;
import android.graphics.LinearGradient;
import android.graphics.Paint;
import android.graphics.Shader;
import android.os.IBinder;
import android.text.Html;
import android.text.Spanned;
import android.util.Log;
import android.widget.RemoteViews;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.androidplot.Plot;
import com.androidplot.ui.LayoutManager;
import com.androidplot.ui.SizeLayoutType;
import com.androidplot.ui.SizeMetrics;
import com.androidplot.ui.XLayoutStyle;
import com.androidplot.ui.YLayoutStyle;
import com.androidplot.ui.widget.Widget;
import com.androidplot.xy.BoundaryMode;
import com.androidplot.xy.SimpleXYSeries;
import com.androidplot.xy.XYGraphWidget;
import com.androidplot.xy.XYSeries;
import com.androidplot.xy.BarFormatter;
import com.androidplot.xy.LineAndPointFormatter;
import com.androidplot.xy.XYPlot;
import com.androidplot.xy.XYStepMode;

public class UpdateWidgetService extends Service {
	private static final String LOG = "gr.auth.med.lomi.ether.widget";
	RemoteViews remoteViews;
	RemoteViews remoteViewsSmall;
	AppWidgetManager appWidgetManager;
	int number = 0;
	static int widgetIdToUse = 0;
	Date dateToDisplay;
	int errorCounter = 0;
	RequestQueue queue;
	Response.Listener<JSONObject> responseListener;
	Context applicationContext;
	private static int measi=0;
	private String []  cmeasText={"SO<sub>2</sub>","NO<sub>2</sub>","PM<sub>10</sub>","CO","O<sub>3</sub>","μέσο PM<sub>10</sub>"};

	@Override
	public void onStart(Intent intent, int startId) {
		Log.i(LOG, "Called");
		// create some random data

		applicationContext = this.getApplicationContext();
		appWidgetManager = AppWidgetManager.getInstance(applicationContext);

		// int[] allWidgetIds = intent
		// .getIntArrayExtra(AppWidgetManager.EXTRA_APPWIDGET_IDS);

		ComponentName thisWidget = new ComponentName(applicationContext,
				EtherWidget.class);

		ComponentName thisWidgetSmall = new ComponentName(applicationContext,
				EtherWidgetSmall.class);
		int[] allSmallWidgetIds = appWidgetManager
				.getAppWidgetIds(thisWidgetSmall);
		int[] allWidgetIds2 = appWidgetManager.getAppWidgetIds(thisWidget);
		// Log.d(LOG, "From Intent" + String.valueOf(allWidgetIds.length));
		Log.d(LOG, "Direct" + String.valueOf(allWidgetIds2.length));

		for (int widgetId : allWidgetIds2) {

			widgetIdToUse = widgetId;

			remoteViews = new RemoteViews(applicationContext.getPackageName(),
					R.layout.widget_layout_std);

			// Get the data from the rest service
			queue = Volley.newRequestQueue(this);
			DateFormat df1 = new SimpleDateFormat("yyyy-MM-dd");

			// Get the date today using Calendar object.
			dateToDisplay = Calendar.getInstance().getTime();
			// Using DateFormat format method we can create a string
			// representation of a date with the defined format.
			String reportDate = df1.format(dateToDisplay);
			Log.d(LOG, "Showing:" + reportDate);
			String []  cmeas={"so2","no2","pm10","co","o3","ypm10"};
		
		
			
			String url="";
			
			url = "http://develop1.med.auth.gr:8080/envapp/jsonSpecific?meas="+cmeas[measi]+"&station=1&days=30";
	
		
			
		Log.d(LOG, "cmeas[i]:" + cmeas[measi]);	
		Log.d(LOG, "[i]:" + measi);		
			//String url = "http://develop1.med.auth.gr:8080/envapp/jsonSpecific?meas="+cmeas[2]+"&station=1&days=30";
					

			Log.d(LOG, "Service URL:" + url);
			// String url =
			// "http://develop1.med.auth.gr:8080/envapp/json?date=2014-3-14";

			responseListener = new Response.Listener<JSONObject>() {

				@Override
				public void onResponse(JSONObject response) {
					Log.d(LOG, "Response => " + response.toString());
					String gentoday="";
					String responseMeas="";
					String dayofgentoday="";
					DateFormat df1 = new SimpleDateFormat("yyyy-MM-dd");
					dateToDisplay = Calendar.getInstance().getTime();
					String todayDate = df1.format(dateToDisplay);
					ArrayList <Number> yvalues = new ArrayList<Number>();
					 Iterator<String> iter = response.keys();
					    while (iter.hasNext()) {
					        String day = iter.next();
					        Log.d(LOG,"day:"+day);
					        try {
					            JSONObject innerValue = response.getJSONObject(day);
					            Iterator<String> iterInner = innerValue.keys();
					            while (iterInner.hasNext()) {
					            	String innerKey = iterInner.next();
					            	Log.d(LOG,"innerKey:"+innerKey);
					            	try {
					            	if (innerKey.equals("gentoday")&&(day.equals(todayDate))){
					            		gentoday=innerValue.getString(innerKey);
					            		dayofgentoday=day;
					            		Log.d(LOG,"gentoday:"+gentoday);	
					            	}else if(innerKey.equals("gentoday")&&(dayofgentoday.compareTo(day)<0)){
					            		gentoday=innerValue.getString(innerKey);
					            		dayofgentoday=day;
					            		Log.d(LOG,"gentoday from another "+dayofgentoday+":"+gentoday);
					            	}else if (!innerKey.equals("gentoday")){
					            	responseMeas=innerKey;
					            	Log.d(LOG,"response meas:"+responseMeas+". value"+innerValue.getString(innerKey));
					            	yvalues.add(Double.valueOf(innerValue.getString(innerKey)));
					            	}
					            	} catch (JSONException e) {
							            // Something went wrong!
							        }
					            }
					        } catch (JSONException e) {
					            // Something went wrong!
					        }
					    }
					    
					   responseMeas=responseMeas.substring(0,responseMeas.length()-2);//remove station info
					    
					Log.d(LOG,responseMeas+":"+yvalues.toString()+":"+gentoday);
					XYPlot plot1 ;
					plot1= new XYPlot(applicationContext, "");
					XYGraphWidget gw= plot1.getGraphWidget();
					
					//metakinisi axis y aristera (-30)
					SizeMetrics sm = new SizeMetrics(0,SizeLayoutType.FILL,
                          0,SizeLayoutType.FILL);
					gw.setSize(sm);
					Paint lineFill1 = new Paint();
					lineFill1.setColor(Color.TRANSPARENT);
			        plot1.setBorderPaint(null);
					plot1.setBackgroundPaint(lineFill1);
					plot1.setDrawingCacheEnabled(true);
					LayoutManager lm = plot1.getLayoutManager();				
					//plot1.setPlotMargins(1, 1, 1, 1);
					//plot1.setPlotPadding(1, 1, 1, 1);
					plot1.layout(0, 0, 170, 110);
					
					ArrayList <Number> dayofweekAl = new ArrayList<Number>();
					
					  for (int i = yvalues.size()-1;i>=0;i--){
						  dayofweekAl.add(i);
					  }
					  Number[] dayofweek=dayofweekAl.toArray(new Number[0]);
				        // an array of years in milliseconds:
					  
				      /*  Number[] yvalues = {
				               1,  // 2001
				                2, // 2002
				                3, // 2003
				                4, // 2004
				                5,
				                6,
				                7
				                // 2005
				        };*/
				        // create our series from our array of nums:
					  Number[] measurements=yvalues.toArray(new Number[0]);
				        XYSeries series2 = new SimpleXYSeries(
				                Arrays.asList(dayofweek),
				                Arrays.asList(measurements),
				                responseMeas);

				        //xrwma axonwn
				      //  plot1.getGraphWidget().getDomainOriginLinePaint().setColor(Color.GRAY);
				       // plot1.getGraphWidget().getRangeOriginLinePaint().setColor(Color.GRAY);
				        
				        
				        String evaluation = gentoday;
				        Date now = new Date();
						DateFormat df = new SimpleDateFormat("EEEE, dd-MM-yyyy");
						String dayString = df.format(now) ;
						//String dateString = DateFormat.getInstance().format(now);
						
						remoteViews.setTextViewText(R.id.graphTitle,Html.fromHtml("Διακύμανση μήνα για "+cmeasText[measi]+"<br/>"));
				        
				        
				        int lineColor = 0;
						if (evaluation.trim().equalsIgnoreCase("Χαμηλά")) {
							
							remoteViews.setImageViewResource(R.id.imageView1,
									R.drawable.spiral_green);
							remoteViews.setTextViewText(R.id.update, dayString+"\n"+"Καλή ποιότητα αέρα");
							lineColor=Color.GREEN;
							Log.d(LOG, "Change layout => green");
						} else if (evaluation.trim().equalsIgnoreCase("Μέτρια")) {
							remoteViews.setImageViewResource(R.id.imageView1,
									R.drawable.spiral_yellow);
							remoteViews.setTextViewText(R.id.update, dayString+"\n"+"Μέτρια ποιότητα αέρα");
							lineColor=Color.YELLOW;							
							Log.d(LOG, "Change layout => yellow");
						} else if (evaluation.trim().equalsIgnoreCase(
								"Αυξημένα")) {
							remoteViews.setImageViewResource(R.id.imageView1,
									R.drawable.spiral_orange);
							remoteViews.setTextViewText(R.id.update, dayString+"\n"+"Αυξημένοι ρύποι");
							lineColor=Color.rgb(255, 79, 0);							
							Log.d(LOG, "Change layout => orange");
						} else if (evaluation.trim().equalsIgnoreCase("Υψηλά")) {
							remoteViews.setImageViewResource(R.id.imageView1,
									R.drawable.spiral_red);
							remoteViews.setTextViewText(R.id.update,dayString+"\n"+ "Πολύ αυξημένοι ρύποι");
							lineColor=Color.RED;
							Log.d(LOG, "Change layout => red");
						} else if (evaluation.trim().equalsIgnoreCase(
								"Πολύ Υψηλά")) {
							remoteViews.setImageViewResource(R.id.imageView1,
									R.drawable.spiral_purple);
							remoteViews.setTextViewText(R.id.update, dayString+"\n"+"Επικίνδυνα αυξημένοι ρύποι");
							lineColor=Color.MAGENTA;
							Log.d(LOG, "Change layout => purple");
						}
				        
				        
				        
				        
				        
				        
				        
				        
				        
				        

				        // Create a formatter to use for drawing a series using LineAndPointRenderer:
				        LineAndPointFormatter series1Format = new LineAndPointFormatter(
				                lineColor,                   // line color
				                Color.TRANSPARENT,                   // point color
				                Color.TRANSPARENT, null);                // fill color
				        series1Format.getLinePaint().setStrokeJoin(Paint.Join.ROUND);
				        series1Format.getLinePaint().setStrokeWidth(2);
				        

				        // setup our line fill paint to be a slightly transparent gradient:
				      //  Paint lineFill = new Paint();
				      //  lineFill.setAlpha(255);
//lineFill.setColor(Color.BLUE);
				        // ugly usage of LinearGradient. unfortunately there's no way to determine the actual size of
				        // a View from within onCreate.  one alternative is to specify a dimension in resources
				        // and use that accordingly.  at least then the values can be customized for the device type and orientation.
				       // lineFill.setShader(new LinearGradient(0, 0, 200, 200, Color.WHITE, Color.GREEN, Shader.TileMode.CLAMP));

				       // LineAndPointFormatter formatter  = new LineAndPointFormatter(Color.rgb(0,0,0), Color.GREEN, Color.RED, null);
				      //  series1Format.setFillPaint(lineFill);
				      
				        plot1.addSeries(series2, series1Format);

				        // draw a domain tick for each year:
				        //plot1.setDomainStep(XYStepMode.SUBDIVIDE, years.length);
				        plot1.setRangeStep(XYStepMode.INCREMENT_BY_VAL,2);
				        //ka8orismos range & domain
				        //plot1.setRangeBoundaries(-180, 359, BoundaryMode.FIXED);
				        //plot1.setDomainBoundaries(0, 8, BoundaryMode.AUTO);
				        //plot1.setRangeBoundaries(3, 10, BoundaryMode.FIXED);

				        // customize our domain/range labels
				       // plot1.setDomainLabel("Year");
				      //  plot1.setRangeLabel("# of Sightings");

				        // get rid of decimal points in our range labels:
				      //  plot1.setRangeValueFormat(new DecimalFormat("0"));
				      //  plot1.getGraphWidget().setGridPaddingLeft(0);
				        plot1.getGraphWidget().setGridPaddingTop(5);
				      //  plot1.getGraphWidget().setPaddingLeft(0);
				      //  plot1.getGraphWidget().setWidth(100);
				        
				        gw.setRangeOriginLabelPaint(null);


				        gw.setRangeLabelWidth(0);

				        
				        gw.setDomainLabelWidth(0);


				        gw.setBackgroundPaint(null);
				        gw.setDomainLabelPaint(null);
				        gw.setRangeLabelPaint(null);
				        gw.setGridBackgroundPaint(null);
				        gw.setDomainOriginLabelPaint(null);
				        gw.setRangeOriginLinePaint(null);
				        gw.setDomainOriginLinePaint(null);
				        gw.setDomainGridLinePaint(null);
				        gw.setRangeGridLinePaint(null);
				        
				        //apokrypsh axis values
				     //   plot1.getLayoutManager().remove(plot1.getDomainLabelWidget());
				     //   plot1.getLayoutManager().remove(plot1.getRangeLabelWidget());
				     //   plot1.getLayoutManager().remove(plot1.getRangeStepValue());
				        //apokrypsh toy legend
				        plot1.getLayoutManager().remove(plot1.getLegendWidget());									
				        
					Bitmap bmp = Bitmap.createBitmap(plot1.getDrawingCache());
					remoteViews.setImageViewBitmap(R.id.plotView1, bmp);
					
					
					
					
					appWidgetManager
							.updateAppWidget(widgetIdToUse, remoteViews);
				}
			};

			Response.ErrorListener errorListener = new Response.ErrorListener() {

				@Override
				public void onErrorResponse(VolleyError error) {
					// TODO Auto-generated method stub
					Log.d(LOG, "Error => " + error.toString());
					remoteViews
							.setTextViewText(R.id.update, "Παρακαλώ περιμένετε...");
					appWidgetManager
							.updateAppWidget(widgetIdToUse, remoteViews);
					errorCounter++;
/*					if (errorCounter <= 5) {
						DateFormat df = new SimpleDateFormat("yyyy-M-dd");
						Calendar cal = Calendar.getInstance();

						cal.add(Calendar.DATE, (-1) * errorCounter);

						// Get the date today using Calendar object.
						dateToDisplay = cal.getTime();
						// Using DateFormat format method we can create a string
						// representation of a date with the defined format.
						String reportDate = df.format(dateToDisplay);
						Log.d(LOG, "Error counter:" + errorCounter);
						Log.d(LOG, "Showing after error:" + reportDate);
						String url = "http://develop1.med.auth.gr:8080/envapp/json?date="
								+ reportDate;

						JsonObjectRequest newRequest = new JsonObjectRequest(
								Request.Method.GET, url, null,
								responseListener, this);
						queue.add(newRequest);

					}else{
						remoteViews
							.setTextViewText(R.id.update, "Δεν βρέθηκαν τιμές για τις τελευταίες πέντε ημέρες...");
						appWidgetManager
								.updateAppWidget(widgetIdToUse, remoteViews);
				
					}*/
					remoteViews
					.setTextViewText(R.id.update, "Δεν βρέθηκαν τιμές...");
				appWidgetManager
						.updateAppWidget(widgetIdToUse, remoteViews);
				}
			};

			JsonObjectRequest jsObjRequest = new JsonObjectRequest(
					Request.Method.GET, url, null, responseListener,
					errorListener);

			queue.add(jsObjRequest);

			// Register an onClickListener
			Intent clickIntent = new Intent(this.getApplicationContext(),
					EtherWidget.class);

			clickIntent.setAction(AppWidgetManager.ACTION_APPWIDGET_UPDATE);
			clickIntent.putExtra(AppWidgetManager.EXTRA_APPWIDGET_IDS,
					allWidgetIds2);

			PendingIntent pendingIntent = PendingIntent.getBroadcast(
					getApplicationContext(), 0, clickIntent,
					PendingIntent.FLAG_UPDATE_CURRENT);
			remoteViews.setOnClickPendingIntent(R.id.layout, pendingIntent);
			
			appWidgetManager.updateAppWidget(widgetId, remoteViews);
			
		}
		if (measi==5){
			measi=0;
		}else{
		measi++;
		}
		// now for smallwidgets
		for (int widgetId : allSmallWidgetIds) {

			widgetIdToUse = widgetId;

			remoteViewsSmall = new RemoteViews(
					applicationContext.getPackageName(),
					R.layout.widget_layout_small);

			// Get the data from the rest service
			queue = Volley.newRequestQueue(this);
			DateFormat df = new SimpleDateFormat("yyyy-M-dd");

			// Get the date today using Calendar object.
			dateToDisplay = Calendar.getInstance().getTime();
			// Using DateFormat format method we can create a string
			// representation of a date with the defined format.
			String reportDate = df.format(dateToDisplay);
			Log.d(LOG, "Showing:" + reportDate);
			String url = "http://develop1.med.auth.gr:8080/envapp/json?date="
					+ reportDate;

			Log.d(LOG, "Service URL:" + url);
			// String url =
			// "http://develop1.med.auth.gr:8080/envapp/json?date=2014-3-14";

			responseListener = new Response.Listener<JSONObject>() {

				@Override
				public void onResponse(JSONObject response) {
					Log.d(LOG, "Response => " + response.toString());
					try {
						String evaluation = response.getString("gentoday");
						if (evaluation.trim().equalsIgnoreCase("Χαμηλά")) {
							remoteViewsSmall.setImageViewResource(
									R.id.imageView1,
									R.drawable.spiral_green);
							Log.d(LOG, "Change layout => green");
						} else if (evaluation.trim().equalsIgnoreCase("Μέτρια")) {
							remoteViewsSmall.setImageViewResource(
									R.id.imageView1,
									R.drawable.spiral_yellow);
							Log.d(LOG, "Change layout => yellow");
						} else if (evaluation.trim().equalsIgnoreCase(
								"Αυξημένα")) {
							remoteViewsSmall.setImageViewResource(
									R.id.imageView1,
									R.drawable.spiral_orange);
							Log.d(LOG, "Change layout => orange");
						} else if (evaluation.trim().equalsIgnoreCase("Υψηλά")) {
							remoteViewsSmall.setImageViewResource(
									R.id.imageView1,
									R.drawable.spiral_red);
							Log.d(LOG, "Change layout => red");
						} else if (evaluation.trim().equalsIgnoreCase(
								"Πολύ Υψηλά")) {
							remoteViewsSmall.setImageViewResource(
									R.id.imageView1,
									R.drawable.spiral_purple);
							Log.d(LOG, "Change layout => purple");
						}

						// remoteViews.setTextViewText(R.id.update, evaluation);
					} catch (JSONException e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}
					appWidgetManager.updateAppWidget(widgetIdToUse,
							remoteViewsSmall);
				}
			};

			Response.ErrorListener errorListener = new Response.ErrorListener() {

				@Override
				public void onErrorResponse(VolleyError error) {
					// TODO Auto-generated method stub
					Log.d(LOG, "Error => " + error.toString());
					remoteViewsSmall.setTextViewText(R.id.update,
							"An error occured");
					appWidgetManager.updateAppWidget(widgetIdToUse,
							remoteViewsSmall);
					errorCounter++;
					if (errorCounter <= 5) {
						DateFormat df = new SimpleDateFormat("yyyy-M-dd");
						Calendar cal = Calendar.getInstance();

						cal.add(Calendar.DATE, (-1) * errorCounter);

						// Get the date today using Calendar object.
						dateToDisplay = cal.getTime();
						// Using DateFormat format method we can create a string
						// representation of a date with the defined format.
						String reportDate = df.format(dateToDisplay);
						Log.d(LOG, "Error counter:" + errorCounter);
						Log.d(LOG, "Showing after error:" + reportDate);
						String url = "http://develop1.med.auth.gr:8080/envapp/json?date="
								+ reportDate;

						JsonObjectRequest newRequest = new JsonObjectRequest(
								Request.Method.GET, url, null,
								responseListener, this);
						queue.add(newRequest);

					}
				}
			};

			JsonObjectRequest jsObjRequest = new JsonObjectRequest(
					Request.Method.GET, url, null, responseListener,
					errorListener);

			queue.add(jsObjRequest);

			// Register an onClickListener
			Intent clickIntent = new Intent(this.getApplicationContext(),
					EtherWidget.class);

			clickIntent.setAction(AppWidgetManager.ACTION_APPWIDGET_UPDATE);
			clickIntent.putExtra(AppWidgetManager.EXTRA_APPWIDGET_IDS,
					allSmallWidgetIds);

			PendingIntent pendingIntent = PendingIntent.getBroadcast(
					getApplicationContext(), 0, clickIntent,
					PendingIntent.FLAG_UPDATE_CURRENT);
			remoteViewsSmall.setOnClickPendingIntent(R.id.imageView1,
					pendingIntent);
			appWidgetManager.updateAppWidget(widgetId, remoteViewsSmall);
		}
		stopSelf();

		super.onStart(intent, startId);
	}

	@Override
	public IBinder onBind(Intent intent) {
		return null;
	}

}
