package mpesa

type Config struct {
	Environment         string
	MpesaConsumerKey    string
	MpesaConsumerSecret string
	B2cConsumerKey      string
	B2cConsumerSecret   string
	Passkey             string
	Shortcode           string
	TillNumber          string
	InitiatorName       string
	InitiatorPassword   string
	B2cShortcode        string
	Callbacks           map[string]string
}

func (c *Config) GetBaseURL() string {
	if c.Environment == "sandbox" {
		return "https://sandbox.safaricom.co.ke"
	}
	return "https://api.safaricom.co.ke"
}
