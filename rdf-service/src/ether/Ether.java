/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package ether;

import com.sun.jersey.api.container.httpserver.HttpServerFactory;
import com.sun.net.httpserver.HttpServer;

/**
 *
 * @author beredim
 */
public class Ether {

    /**
     * @param args the command line arguments
     */
    
    static String BASE_URI = "http://localhost:3333/";
    public static void main(String[] args) throws Exception {
        HttpServer server = HttpServerFactory.create(BASE_URI);
        server.start();
        System.out.println("Press Enter to stop the server. ");
        System.in.read();
        server.stop(0);
    }
}
