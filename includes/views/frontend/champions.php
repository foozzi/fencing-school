<?

class View_Frontend_Champions extends View_Frontend
{
    public function index()
    {
        return $this->includeLayout('view/champ/index.html');
    }
    
    public function man()
    {
        return $this->includeLayout('view/champ/man.html');
    }
    
    public function woman()
    {
        return $this->includeLayout('view/champ/woman.html');
    }
    
    public function man_one()
    {
        return $this->includeLayout('view/champ/man_one.html');
    }
    
    public function woman_one()
    {
        return $this->includeLayout('view/champ/woman_one.html');
    }
}