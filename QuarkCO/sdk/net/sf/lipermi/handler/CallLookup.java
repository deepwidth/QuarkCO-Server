/*
 * LipeRMI - a light weight Internet approach for remote method invocation
 * Copyright (C) 2006  Felipe Santos Andrade
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * For more information, see http://lipermi.sourceforge.net/license.php
 * You can also contact author through lipeandrade@users.sourceforge.net
 */

package net.sf.lipermi.handler;

import java.net.Socket;
import java.util.Collections;
import java.util.HashMap;
import java.util.Map;

/**
 * A common static way to access the Socket
 * which started the Delegator Thread. A very useful
 * way to know who called the current Method.
 *
 * @author lipe
 * @date   07/10/2006
 *
 * @see       net.sf.lipermi.handler.CallHandler
 */
public class CallLookup {

    private static Map<Thread, ConnectionHandler> connectionMap = Collections.synchronizedMap(new HashMap<Thread, ConnectionHandler>());

    /**
     * Get the current Socket for this call.
     * Only works in the main thread call.
     *
     * @return Socket which started the Delegator Thread
     */
    public static Socket getCurrentSocket() {
        ConnectionHandler handler = connectionMap.get(Thread.currentThread());
        return (handler == null ? null : handler.getSocket());
    }

    static void handlingMe(ConnectionHandler connectionHandler) {
        synchronized (connectionMap) {
            connectionMap.put(Thread.currentThread(), connectionHandler);
        }
    }

    static void forgetMe() {
        synchronized (connectionMap) {
            connectionMap.remove(Thread.currentThread());
        }
    }
}

// vim: ts=4:sts=4:sw=4:expandtab
