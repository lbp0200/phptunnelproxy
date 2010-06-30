package cc.co.phptunnelproxy.ptplocal;

import java.io.FileInputStream;
import java.io.IOException;
import java.net.InetSocketAddress;
import java.net.MalformedURLException;
import java.net.Proxy;
import java.net.SocketAddress;
import java.net.URL;
import java.util.Properties;

import org.apache.log4j.Logger;

public class Config {
	private static Logger log = Logger.getLogger(Config.class);

	private static Config ins = new Config();

	private Proxy proxy = null;

	Properties prop = null;
	Properties ipMap = null;
	Properties appProp = null;

	private Config() {
		prop = new Properties();
		ipMap = new Properties();
		appProp = new Properties();
		try {
			prop.load(new FileInputStream("etc/ptp.properties"));

			ipMap.load(new FileInputStream("etc/ipmap.properties"));

			appProp.load(Config.class
					.getResourceAsStream("/etc/app.properties"));
		} catch (IOException e) {
			log.error(e.getMessage(), e);
		}

		// build proxy object
		boolean useProxy = Boolean.parseBoolean(this.getValue(
				"ptp.local.bypass.proxy.inuse", "false"));
		if (useProxy) {
			Proxy.Type proxyType = Proxy.Type.valueOf(this.getValue(
					"ptp.local.bypass.proxy.type", "HTTP").toUpperCase());
			String proxyHost = appProp.getProperty(
					"ptp.local.bypass.proxy.host", "127.0.0.1");
			int proxyPort = Integer.parseInt(this.getValue(
					"ptp.local.bypass.proxy.port", "8080"));
			SocketAddress proxyAddress = new InetSocketAddress(proxyHost,
					proxyPort);
			proxy = new Proxy(proxyType, proxyAddress);
		} else {
			proxy = Proxy.NO_PROXY;
		}
	}

	public static synchronized Config getIns() {
		return ins;
	}

	public String getValue(String key) {
		return prop.getProperty(key);
	}

	public String getValue(String key, String defaultValue) {
		return prop.getProperty(key, defaultValue);
	}

	public URL getRemotePhpURL() {
		try {
			return new URL(this.getValue("ptp.remote.php",
					"http://s1.phptunnelproxy.co.cc/ptpremote/remote.php"));
		} catch (MalformedURLException e) {
			log.fatal("ptp.remote.php config syntax error!", e);
			Thread.currentThread().interrupt();
		}
		return null;
	}

	public String getIp(String domain) {
		return ipMap.getProperty(domain, domain);
	}

	public String getVersion() {
		return appProp.getProperty("build.version", "0.0.0");
	}

	public String getCompileDate() {
		return appProp.getProperty("app.compile.time", "2012-12-21 00:00");
	}

	public String getUserAgent() {
		return "PHPTunnelProxy Local/" + getVersion() + " (" + getCompileDate()
				+ ")";
	}

	public Proxy getProxy() {
		log.info("use proxy: " + proxy.toString());
		return proxy;
	}

}