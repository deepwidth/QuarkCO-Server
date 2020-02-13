import net.sf.lipermi.exception.LipeRMIException;
import net.sf.lipermi.handler.CallHandler;
import net.sf.lipermi.net.Server;

import me.zkk.kkapp.ExampleService;
import me.zkk.kkapp.ExampleServiceImpl;
import java.io.IOException;

public class MainServer {

    public static void main(String[] args) {
        CallHandler callHandler = new CallHandler();
        ExampleService exampleService;
	exampleService = new ExampleServiceImpl();
        try {
            callHandler.registerGlobal(ExampleService.class, exampleService);
        } catch(LipeRMIException e) {
            e.printStackTrace();
        }
        Server server = new Server();
        int thePortIWantToBind = 4455;

        try {
            server.bind(thePortIWantToBind, callHandler);
        } catch (IOException e) {
            e.printStackTrace();
        }
	System.out.println("Server is running!");
    }
}
