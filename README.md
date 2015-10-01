# Munin-for-Android-GCM-Proxy
Symfony2 server application whose aim is to transmit alerts from munin server to GCM

	+----------------+                                                    
	|  munin master  +--+                                                 
	+----------------+  |                                                 
	                    |                                                 
	+----------------+  |   +-----------+                                 
	|  munin master  +----> | GCM proxy |             +----------------+  
	+----------------+  |   +-----+-----+        +--> | Android device |  
	                    |         |              |    +----------------+  
	+----------------+  |         v              |                        
	|  munin master  +--+        ++------------+ |    +----------------+  
	+----------------+           | GCM servers +----> | Android device |  
	                             +-------------+ |    +----------------+  
	                                             |                        
	                                             |    +----------------+  
	                                             +--> | Android device |  
	                                                  +----------------+
