import { defineConfig } from "vite";
import fs from "node:fs";
import path from "node:path";

const HOT_PORT = 5173;
const HOT_FILE = path.join("vendor", "lakm", "commenter", "commenter.hot");
const BUILD_DIR = path.join("vendor", "lakm", "commenter", "build");

function findLaravelRoot(startDir) {
    let dir = startDir;

    for (let i = 0; i < 12; i++) {
        if (fs.existsSync(path.join(dir, "artisan"))) {
            return dir;
        }

        const parent = path.dirname(dir);

        if (parent === dir) {
            return null;
        }

        dir = parent;
    }

    return null;
}

const appRoot = findLaravelRoot(process.cwd());
const cwd = fs.realpathSync(process.cwd());

if (process.cwd() !== cwd) {
    process.chdir(cwd);
}

const publicPathOf = (relativePath) =>
    path.join(appRoot ?? cwd, "public", relativePath);

function commenterHotPlugin() {
    let hotFilePath;

    const resolveHotFile = () => {
        hotFilePath = publicPathOf(HOT_FILE);

        return hotFilePath;
    };

    const cleanupHotFile = () => {
        try {
            fs.rmSync(hotFilePath || publicPathOf(HOT_FILE), { force: true });
        } catch (_error) {
            // ignore
        }
    };

    const writeHotFile = (server) => {
        resolveHotFile();

        const address = server.httpServer.address();
        const port =
            typeof address === "object" && address ? address.port : HOT_PORT;

        fs.mkdirSync(path.dirname(hotFilePath), { recursive: true });
        fs.writeFileSync(hotFilePath, `http://localhost:${port}`);
    };

    return {
        name: "commenter-hot-file",
        apply: "serve",
        configureServer(server) {
            server.httpServer?.once("listening", () => {
                writeHotFile(server);
                server.config.logger.info(`commenter.hot -> ${hotFilePath}`);
            });

            const exitHandler = () => {
                cleanupHotFile();
            };

            process.once("SIGINT", exitHandler);
            process.once("SIGTERM", exitHandler);
            process.once("exit", cleanupHotFile);
        },
    };
}

export default defineConfig({
    base: "",
    root: cwd,
    publicDir: false,
    plugins: [commenterHotPlugin()],
    build: {
        outDir: appRoot
            ? publicPathOf(BUILD_DIR)
            : path.join(cwd, "public", "build"),
        emptyOutDir: true,
        manifest: "manifest.json",
        rollupOptions: {
            input: {
                "resources/css/app.css": "resources/css/app.css",
                "resources/js/app.js": "resources/js/app.js",
            },
        },
    },
    server: {
        port: HOT_PORT,
        host: "0.0.0.0",
        cors: true,
        hmr: {
            host: "localhost",
        },
        watch: {
            followSymlinks: false,
        },
    },
});
