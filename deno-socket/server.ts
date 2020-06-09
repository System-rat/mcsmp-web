import { serve } from 'https://deno.land/std/http/mod.ts';
import {
    acceptWebSocket,
    isWebSocketCloseEvent,
    isWebSocketPingEvent
} from 'https://deno.land/std/ws/mod.ts';

// @ts-ignore
const key = Deno.args[1];
const port = Deno.args[2] || 3621;

console.log(`Started server on port ${port} with key ${key}`);

for await (const req of serve(`:${port}`)) {
    const { conn, w: writer, r: reader, headers, url } = req;

    if (url === `/connect?key=${key}`) {
        try {
            const sock = await acceptWebSocket({
                conn,
                bufReader: reader,
                bufWriter: writer,
                headers
            });

            console.log(`New connection`);

            try {
                for await (const ev of sock) {
                    if (typeof ev === 'string') {
                        console.log(`Message: ${ev}`);
                        await sock.send('bruh');
                    } else if (isWebSocketCloseEvent(ev)) {
                        console.log(`Client closed: ${ev.code} ${ev.reason}`)
                    } else if (isWebSocketPingEvent(ev)) {
                        console.log(`Ping: ${ev}`);
                    }
                }
            } catch (e) {
                console.error(`Failed to recieve frame: ${e}`);

                if (!sock.isClosed) {
                    await sock.close(1000).catch(console.error);
                }
            }
        } catch (e) {
            console.error(`Failed to establish ws: ${e}`);

            await req.respond({
                body: 'Failed to establish ws',
                status: 500
            });
        }
    } else {
        req.respond({
            body: 'Unauthorized',
            status: 403
        })
    }

}